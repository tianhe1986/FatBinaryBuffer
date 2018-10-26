<?php

namespace FatBinaryBuffer\Tests;

use PHPUnit\Framework\TestCase;
use FatBinaryBuffer\FatBinaryBuffer;

class FatExcelTest extends TestCase
{
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
    
    public function testMixedBigEndian()
    {
        
    }
}
