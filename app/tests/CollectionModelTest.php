<?php

class CollectionModelTest extends TestCase {
	public function testCanReadDummyDataUsingDummyDataClass (){
		$msg = DummyData::all()->first()->message;
		$expectedMsg = 'You did it!';
		$this->assertEquals($msg, $expectedMsg);
	}

	
}

?>