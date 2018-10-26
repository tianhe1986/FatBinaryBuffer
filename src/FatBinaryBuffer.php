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
        $this->isSystemBigEndian = (pack("L", 1) === pack("N", 1));
        $this->isDiffOrder = ($this->isBigEndian xor $this->isSystemBigEndian);
    }
    
    public function setBuffer($buffer)
    {
        $this->buffer = $buffer;
        $this->len = strlen($buffer);
        $this->offset = 0;
    }
    
    public function rewind()
    {
        $this->setOffset(0);
    }
    
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }
    
    public function getOffset()
    {
        return $this->offset;
    }
    
    public function getLength()
    {
        return $this->len;
    }
    
    public function readUInt32()
    {
        $str = $this->readFromBuffer(4);
        $array = unpack($this->isBigEndian ? "Nval" : "Vval", $str);
        return $array["val"];
    }
    
    public function writeUInt32($val)
    {
        $str = pack($this->isBigEndian ? "N" : "V", $val);
        $this->writeToBuffer($str);
        
        return $this;
    }
    
    public function readInt32()
    {
        $str = $this->readFromBuffer(4);
        if ($this->isDiffOrder) {
            $str = strrev($str);
        }
        $array = unpack("lval", $str);
        return $array["val"];
    }
    
    public function writeInt32($val)
    {
        $str = pack("l", $val);
        if ($this->isDiffOrder) {
            $str = strrev($str);
        }
        $this->writeToBuffer($str);
        
        return $this;
    }
    
    protected function readFromBuffer($len)
    {
        $toOffset = $this->offset + $len;
        if ($toOffset > $this->len) {
            throw new Exception("len exceed");
        }
        $str = substr($this->buffer, $this->offset, $len);
        $this->offset = $toOffset;
        
        return $str;
    }
    
    protected function writeToBuffer($val)
    {
        if ($this->offset === $this->len) { //在末尾
            $this->buffer .= $val;
            $this->offset = $this->len = ($this->offset + strlen($val));
        } else { //拼接
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