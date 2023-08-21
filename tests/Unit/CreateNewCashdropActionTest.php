<?php

namespace Tests\Unit;

use App\Actions\Cashdrop\CreateNewCashdropRoundAction;
use App\Models\Cashdrop;
use App\Repositories\Cashingames\CashdropRepository;
use App\Services\Firebase\FirestoreService;
use Database\Seeders\CashDropSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateNewCashdropActionTest extends TestCase
{   
    use RefreshDatabase;
    public $cashdrop;
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CashDropSeeder::class);
        $this->cashdrop = Cashdrop::first();
        
    }

    public function test_new_cashdrop_rounds_can_be_created(): void
    {   
        config(['trivia.cashdrops_firestore_document_id' => "randomId12345"]);
        $action= new CreateNewCashdropRoundAction(
            new CashdropRepository, 
            $this->mockFirestoreService());

    
        $action->execute($this->cashdrop);

        $this->assertDatabaseHas('cashdrop_rounds', [
            'cashdrop_id' => $this->cashdrop->id,
            'percentage_stake' => ($this->cashdrop->percentage_stake ),
            'pooled_amount' => 0.0
        ]);
    }
    private function mockFirestoreService()
    {
        return $this->createMock(FirestoreService::class);
    }
}
