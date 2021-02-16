<?php

class AddKeysToContractTable extends Migration
{
    public function description()
    {
        return 'Add keys user_id and supervisor to contract table';
    }

    public function up()
    {   
        $db = DBManager::get();
        $db->exec("ALTER TABLE `stundenzettel_contracts`
                  ADD KEY `user_id` (`user_id`),
                  ADD KEY `supervisor` (`supervisor`)");

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        
    }
}

