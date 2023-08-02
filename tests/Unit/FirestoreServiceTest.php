<?php

namespace Tests\Unit;

use App\Services\Firebase\FirestoreService;
use Tests\TestCase;

use Mockery\MockInterface;

class FirestoreServiceTest extends TestCase
{   
    public $_service;
        
    protected function setUp(): void
    {   
        parent::setUp();
        $this->mock(FirestoreService::class, function (MockInterface $mock) {
            $mock->shouldReceive('document');
        });
        $this->_service = new FirestoreService();
        
    }
    public function test_that_document_can_be_created()
    {   
        
        $this->assertNull( $this->_service->createDocument('testDocument','documentstring',[], 'development'));
        
    }
    public function test_that_document_can_be_updated()
    {   

        $this->assertNull( $this->_service->updateDocument('testDocument','documentstring',[], 'development'));
        
    }
    public function test_that_document_can_be_fetched()
    {   

        $this->assertIsArray( $this->_service->getDocument('testDocument','documentstring', 'development'));
        
    }
    public function test_that_document_can_be_deleted()
    {   

        $this->assertNull( $this->_service->deleteDocument('testDocument','documentstring', 'development'));
        
    }
}
