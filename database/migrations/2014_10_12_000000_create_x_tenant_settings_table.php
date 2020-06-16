<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXTenantSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('x_tenant_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('super_admin_subdomain')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->string('name')->nullable();
            $table->boolean('allow_www')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenants');
    }
}
