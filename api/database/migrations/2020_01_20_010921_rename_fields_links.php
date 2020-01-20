<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameFieldsLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('links', function (Blueprint $table) {
            $table->renameColumn('userId', 'user_id');
            $table->renameColumn('idURL', 'url_id');
            $table->renameColumn('realURL', 'real_url');
            $table->renameColumn('countHit', 'count_hit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('links', function (Blueprint $table) {
            $table->renameColumn('user_id', 'userId');
            $table->renameColumn('url_id', 'idURL');
            $table->renameColumn('real_url', 'realURL');
            $table->renameColumn('count_hit', 'countHit');
        });
    }
}
