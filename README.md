# FatBinaryBuffer
FatBinaryBuffer is a lightweight library for reading/writing binary string.

#Requires
PHP 7.1 or higher

#Installation
<code>
 composer require tianhe1986/fatbinarybuffer
</code>

and then in your code

<code>
require_once __DIR__ . '/vendor/autoload.php';
use FatBinaryBuffer\FatBinaryBuffer;
</code>

#Support
It supports char/unsigned char, short/unsigned short, int32/uint32, int64/uint64, string, string with length.  
**Do not support float and double!**

#Usage
### big endian vs little endian
<code>
$binaryBufferBE = new FatBinaryBuffer(true); //big endian
$binaryBufferLE = new FatBinaryBuffer(false); //little endian
</code>

### buffer opration
<code>
$binaryBuffer = new FatBinaryBuffer();

//set buffer, would calculate length and offset automaticly
$buffer = base64_decode('XXXXXX');
$binaryBuffer->setBuffer($buffer);

//get buffer
$buffer = $binaryBuffer->getBuffer();

// clear buffer
$binaryBuffer->clear();

// set offset, and then continue to read/write from this position, take byte as unit
$binaryBuffer->setOffset(4); // read/write from the 4th byte

// rewind, just set offset 0
$binaryBuffer->rewind();
</code>

### writing data
<code>
$binaryBuffer = new FatBinaryBuffer(true);

// char and unsigned char, must pass a number, not a char, or you can use `ord` function for transformation
$binaryBuffer->writeChar(100);
$binaryBuffer->writeUChar(225);

// short and unsigned short
$binaryBuffer->writeShort(-1234);
$binaryBuffer->writeUShort(1236);

// int32 and uint32
$binaryBuffer->writeInt32(-999);
$binaryBuffer->writeUInt32(1098);

// int64 and uint64
$binaryBuffer->writeInt64(-98765);
$binaryBuffer->writeUInt64(888);

// string, write its length as a uint32 value first, and then the real string
$binaryBuffer->writeString("张学友！张学友！我们爱你！");

// string with given length, padding with NUL
$binaryBuffer->writeStringByLength("Hello world", 3); // would get 'Hel' when reading
</code>

### reading data
<code>
$newBuffer = new FatBinaryBuffer();
$newBuffer->setBuffer($binaryBuffer->getBuffer());

// char and unsigned char
$char = $newBuffer->readChar();
$uchar = $newBuffer->readUChar();

// short and unsigned short
$short = $newBuffer->readShort();
$ushort = $newBuffer->readUShort();

// int32 and uint32
$a = $newBuffer->readInt32();
$b = $newBuffer->readUInt32();

// int64 and uint64
$a = $newBuffer->readInt64();
$b = $newBuffer->readUInt64();

// string, read its length first, and then the real string
$str = $newBuffer->readString();

// read string with given length, would remove NUL at the end of the string
$str = $newBuffer->readStringByLength(3);
</code>

### combine with websocket server
Taking [Workerman](https://github.com/walkor/workerman) for exmaple.  
<code>
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;
use Workerman\Protocols\Websocket;
use FatBinaryBuffer\FatBinaryBuffer;

$ws_worker = new Worker("websocket://0.0.0.0:13001");

// 4 processes
$ws_worker->count = 4;

$ws_worker->onConnect = function($connection)
{
    $connection->onWebSocketConnect = function($connection , $http_header)
    {
        $connection->websocketType = Websocket::BINARY_TYPE_ARRAYBUFFER;
    };
};

$ws_worker->onMessage = function($connection, $data)
{
    $socketData = new FatBinaryBuffer(false);
    $socketData->setBuffer($data);
    $opcode = $socketData->readUInt32();
    echo $opcode."\n";
    echo $socketData->readUInt32()."\n";
    echo $socketData->readUInt32()."\n";
    if ($opcode === 21102) {
        echo $socketData->readUInt32()."\n";
        $newData = new FatBinaryBuffer(false);
        $newData->writeUInt32(21103);
        $newData->writeUInt32(16);
        $newData->writeUInt32(11);
        $newData->writeUInt32(777);
        $connection->send($newData->getBuffer());
    } else if ($opcode === 21104) {
        $len = $socketData->readUShort();
        echo "string len ".$len."\n";
        echo $socketData->readStringByLength($len)."\n";
        echo $socketData->readChar()."\n";
        echo $socketData->readUShort()."\n";
        echo $socketData->readInt32()."\n";
        echo $socketData->readStringByLength(5)."\n";
    }
};
Worker::runAll();
</code>

Using [Layabox](https://www.layabox.com/) as a typescript client for testing. It works fine.  
<code>
import Socket = Laya.Socket;
import Byte = Laya.Byte;

class TestSocket {
    private socket: Socket;
    private output: Byte;
    private byte: Byte;
    private headerLength:number = 12;

    constructor() {
        this.byte = new Byte();
        this.byte.endian = Byte.LITTLE_ENDIAN;
        this.connect();
    }

    private connect(): void {
        this.socket = new Socket();
        this.socket.connectByUrl("ws://localhost:13001");

        this.output = this.socket.output;
        

        this.socket.on(Laya.Event.OPEN, this, this.onSocketOpen);
        this.socket.on(Laya.Event.CLOSE, this, this.onSocketClose);
        this.socket.on(Laya.Event.MESSAGE, this, this.onMessageReveived);
        this.socket.on(Laya.Event.ERROR, this, this.onConnectError);
    }

    private onSocketOpen(): void {
        console.log("Connected");

        this.byte.clear();
        this.byte.writeUint32(21102);
        this.byte.writeUint32(16);
        this.byte.writeUint32(657);
        this.byte.writeUint32(10);

        this.socket.send(this.byte.buffer);
        this.byte.clear();
    }

    private onSocketClose(): void {
        console.log("Socket closed");
    }

    private onMessageReveived(message: any): void {
        console.log("Message from server:");
        if (typeof message == "string") {
            console.log(message);
        }
        else if (message instanceof ArrayBuffer) {
            let byte = new Byte(message);
            let messageId = byte.getUint32();
			let messageLength = byte.getUint32() - this.headerLength;
			let userId = byte.getUint32();

			console.log(messageId, messageLength, userId);
            this.handleMessage(byte, messageId);
        }
        this.socket.input.clear();
    }

    private onConnectError(e: Event): void {
        console.log("error");
    }

    protected handleMessage(byte:Byte, messageId:number):void
    {
        if (messageId === 21103) {
            let serverTime:number = byte.getUint32();
            console.log("recieve heart beat " + serverTime);

            this.byte.clear();
            let str = "You are shock!我爱黎明！！！";
            let newByte = new Byte();
            newByte.writeUTFString(str);
            newByte.writeByte(111);
            newByte.writeUint16(65500);
            newByte.writeInt32(-70);
            newByte.writeUTFBytes("topod");

            let len = this.headerLength + newByte.length;
            this.byte.writeUint32(21104);
            this.byte.writeUint32(len);
            this.byte.writeUint32(657);
            this.byte.writeArrayBuffer(newByte.buffer);

            this.socket.send(this.byte.buffer);
            this.byte.clear();
        }
    }
}
</code>