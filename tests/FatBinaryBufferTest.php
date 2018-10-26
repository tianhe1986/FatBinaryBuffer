<?php

namespace FatBinaryBuffer\Tests;

use PHPUnit\Framework\TestCase;
use FatBinaryBuffer\FatBinaryBuffer;

class FatExcelTest extends TestCase
{
    protected $isSystemBigEndian;
    
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->isSystemBigEndian = (pack("L", 1) === pack("N", 1));
    }
    public function testUInt32()
    {
        $a = 123;
        $binaryBufferBE = new FatBinaryBuffer(true);
        $binaryBufferLE = new FatBinaryBuffer(false);
        
        $binaryBufferBE->writeUInt32($a);
        $this->assertEquals(4, $binaryBufferBE->getLength());
        $this->assertEquals(pack("N", $a), $binaryBufferBE->getBuffer());
        
        $binaryBufferLE->writeUInt32($a);
        $this->assertEquals(4, $binaryBufferLE->getLength());
        $this->assertEquals(pack("V", $a), $binaryBufferLE->getBuffer());
        
        $binaryBufferBE->rewind();
        $binaryBufferLE->rewind();
        
        $this->assertEquals($a, $binaryBufferBE->readUInt32());
        $this->assertEquals($a, $binaryBufferLE->readUInt32());
    }
    
    public function testInt32()
    {
        $a = -123;
        $binaryBufferBE = new FatBinaryBuffer(true);
        $binaryBufferLE = new FatBinaryBuffer(false);
        
        $packed = pack("l", $a);
        $binaryBufferBE->writeInt32($a);
        $this->assertEquals(4, $binaryBufferBE->getLength());
        $this->assertEquals($this->isSystemBigEndian ? $packed : strrev($packed), $binaryBufferBE->getBuffer());
        
        $binaryBufferLE->writeInt32($a);
        $this->assertEquals(4, $binaryBufferLE->getLength());
        $this->assertEquals($this->isSystemBigEndian ? strrev($packed) : $packed, $binaryBufferLE->getBuffer());
        
        $binaryBufferBE->rewind();
        $binaryBufferLE->rewind();
        
        $this->assertEquals($a, $binaryBufferBE->readInt32());
        $this->assertEquals($a, $binaryBufferLE->readInt32());
    }
    
    public function testMixedBigEndian()
    {
        
    }
}
