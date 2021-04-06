<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\WalletTransaction;

class MigrateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates relevant data from version 1 database to version 2 database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
    
        $v1_database = DB::connection('mysql-database-v1');
        
        $tables = ['users','profiles','wallets','user_plan','games'];
            
            foreach($tables as $table){
                foreach($v1_database->table($table)->get() as $data){ 
                    DB::table($table)->insert((array) $data); 
                }
    
            }
        //insert into wallet transactions
        $v1_wallet_transactions = $v1_database->table('wallet_transactions')->get();
            foreach($v1_wallet_transactions as $data){
                DB::table('wallet_transactions')->insert([
                    'wallet_id' => $data->wallet_id,
                    'transaction_type' => $data->transaction_type,
                    'amount' => $data->amount,
                    'balance' => $data->balance,
                    'wallet_kind'=>'CREDITS',
                    'description' => $data->description,
                    'reference' => $data->reference,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at
                ]);
            }

        //subscribe all users to free plan
        for($i = 1; $i<=71; $i++){
            DB::table('user_plan')->insert([
                'user_id' => $i,
                'plan_id' => 4,
                'used' => 0,
                'is_active' => true
                ]);
        }
              
    }
}
