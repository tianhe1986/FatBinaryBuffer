<?php

namespace FatBinaryBuffer;

class FatBinaryBuffer
{
    // real buffer
    protected $buffer = '';
    
    // now position
    protected $offset = 0;
    
    // buffer length
    protected $len = 0;
    
    // big endian or little endian
    protected $isBigEndian = true;
    
    protected $isSystemBigEndian;
    
    protected $isDiffOrder;
    
    public function __construct($isBigEndian = true)
    {
        $this->isBigEndian = $isBigEndian;
        $this->isSystemBigEndian = (pack("S", 1) === pack("n", 1));
        $this->isDiffOrder = ($this->isBigEndian xor $this->isSystemBigEndian);
    }
    
    public function setBuffer($buffer)
    {
        $this->buffer = $buffer;
        $this->len = strlen($buffer);
        $this->offset = 0;
        
        return $this;
    }
    
    public function rewind()
    {
        $this->setOffset(0);
        
        return $this;
    }
    
    public function clear()
    {
        $this->buffer = '';
        $this->len = $this->offset = 0;
        
        return $this;
    }
    
    public function setOffset($offset)
    {
        $this->offset = $offset;
        
        return $this;
    }
    
    public function getOffset()
    {
        return $this->offset;
    }
    
    public function getLength()
    {
        return $this->len;
    }
    
    public function readUChar()
    {
        $str = $this->readFromBuffer(1);
        $array = unpack("Cval", $str);
        return $array["val"];
    }
    
    public function writeUChar($val)
    {
        $str = pack("C", $val);
        $this->writeToBuffer($str);
        
        return $this;
    }
    
    public function readChar()
    {
        $str = $this->readFromBuffer(1);
        $array = unpack("cval", $str);
        return $array["val"];
    }
    
    public function writeChar($val)
    {
        $str = pack("c", $val);
        $this->writeToBuffer($str);
        
        return $this;
    }
    
    public function readUShort($isBigEndian = null)
    {
        if ($isBigEndian === null) {
            $isBigEndian = $this->isBigEndian;
        }
        $str = $this->readFromBuffer(2);
        $array = unpack($isBigEndian ? "nval" : "vval", $str);
        return $array["val"];
    }
    
    public function writeUShort($val, $isBigEndian = null)
    {
        if ($isBigEndian === null) {
            $isBigEndian = $this->isBigEndian;
        }
        $str = pack($isBigEndian ? "n" : "v", $val);
        $this->writeToBuffer($str);
        
        return $this;
    }
    
    public function readShort($isBigEndian = null)
    {
        $str = $this->readFromBuffer(2);
        $isDiffOrder = (($isBigEndian === null) ? $this->isDiffOrder : ($isBigEndian xor $this->isSystemBigEndian));
        if ($isDiffOrder) {
            $str = strrev($str);
        }
        $array = unpack("sval", $str);
        return $array["val"];
    }
    
    public function writeShort($val, $isBigEndian = null)
    {
        $str = pack("s", $val);
        $isDiffOrder = (($isBigEndian === null) ? $this->isDiffOrder : ($isBigEndian xor $this->isSystemBigEndian));
        if ($isDiffOrder) {
            $str = strrev($str);
        }
        $this->writeToBuffer($str);
        
        return $this;
    }
    
    public function readUInt32($isBigEndian = null)
    {
        if ($isBigEndian === null) {
            $isBigEndian = $this->isBigEndian;
        }
        $str = $this->readFromBuffer(4);
        $array = unpack($isBigEndian ? "Nval" : "Vval", $str);
        return $array["val"];
    }
    
    public function writeUInt32($val, $isBigEndian = null)
    {
        if ($isBigEndian === null) {
            $isBigEndian = $this->isBigEndian;
        }
        $str = pack($isBigEndian ? "N" : "V", $val);
        $this->writeToBuffer($str);
        
        return $this;
    }
    
    public function readInt32($isBigEndian = null)
    {
        $str = $this->readFromBuffer(4);
        $isDiffOrder = (($isBigEndian === null) ? $this->isDiffOrder : ($isBigEndian xor $this->isSystemBigEndian));
        if ($isDiffOrder) {
            $str = strrev($str);
        }
        $array = unpack("lval", $str);
        return $array["val"];
    }
    
    public function writeInt32($val, $isBigEndian = null)
    {
        $str = pack("l", $val);
        $isDiffOrder = (($isBigEndian === null) ? $this->isDiffOrder : ($isBigEndian xor $this->isSystemBigEndian));
        if ($isDiffOrder) {
            $str = strrev($str);
        }
        $this->writeToBuffer($str);
        
        return $this;
    }
    
    public function readUInt64($isBigEndian = null)
    {
        if ($isBigEndian === null) {
            $isBigEndian = $this->isBigEndian;
        }
        $str = $this->readFromBuffer(8);
        $array = unpack($isBigEndian ? "Jval" : "Pval", $str);
        return $array["val"];
    }
    
    public function writeUInt64($val, $isBigEndian = null)
    {
        if ($isBigEndian === null) {
            $isBigEndian = $this->isBigEndian;
        }
        $str = pack($isBigEndian ? "J" : "P", $val);
        $this->writeToBuffer($str);
        
        return $this;
    }
    
    public function readInt64($isBigEndian = null)
    {
        $str = $this->readFromBuffer(8);
        $isDiffOrder = (($isBigEndian === null) ? $this->isDiffOrder : ($isBigEndian xor $this->isSystemBigEndian));
        if ($isDiffOrder) {
            $str = strrev($str);
        }
        $array = unpack("qval", $str);
        return $array["val"];
    }
    
    public function writeInt64($val, $isBigEndian = null)
    {
        $str = pack("q", $val);
        $isDiffOrder = (($isBigEndian === null) ? $this->isDiffOrder : ($isBigEndian xor $this->isSystemBigEndian));
        if ($isDiffOrder) {
            $str = strrev($str);
        }
        $this->writeToBuffer($str);
        
        return $this;
    }
    
    public function readStringByLength($length)
    {
        $str = $this->readFromBuffer($length);
        $array = unpack("a{$length}val", $str);
        
        if (ord($array["val"][$length - 1]) !== 0) {
            return $array["val"];
        }
        
        $index = $length - 2;
        while (ord($array["val"][$index]) === 0) {
            $index--;
            if ($index === 0) {
                break;
            }
        }
        
        return substr($array["val"], 0, $index + 1);
    }
    
    public function writeStringByLength($val, $length = 0)
    {
        if ($length === 0) {
            $length = strlen($val);
        }
        
        $str = pack("a{$length}", $val);
        $this->writeToBuffer($str);
        
        return $this;
    }
    
    // read length first, then real string
    public function readString()
    {
        $length = $this->readUInt32(true);
        
        return $this->readStringByLength($length);
    }
    
    //write length first, then real string
    public function writeString($val)
    {
        $length = strlen($val);
        
        return $this->writeUInt32($length, true)->writeStringByLength($val, $length);
    }
    
    protected function readFromBuffer($len)
    {
        $toOffset = $this->offset + $len;
        if ($toOffset > $this->len) {
            throw new \Exception("len exceed", -101);
        }
        $str = substr($this->buffer, $this->offset, $len);
        $this->offset = $toOffset;
        
        return $str;
    }
    
    protected function writeToBuffer($val)
    {
        if ($this->offset === $this->len) { //at the end
            $this->buffer .= $val;
            $this->offset = $this->len = ($this->offset + strlen($val));
        } else { // concat
            $len = strlen($val);
            $this->buffer = substr($this->buffer, 0, $this->offset) . $val . substr($this->buffer, $this->offset + $len);
            $this->offset += $len;
            if ($this->offset > $this->len) {
                $this->len = $this->offset;
            }
        }
    }
    
    public function getBuffer()
    {
        return $this->buffer;
    }
}