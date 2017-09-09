<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('persons', function(Blueprint $table){
            $table->increments('id');
            $table->boolean('company')->default(false);
            $table->string('name',300);
            $table->string('company_name',350)->nullable();
            $table->string('cpf_cnpj',20)->nullable()->unique();
            $table->string('rg_ie',30)->nullable();
            $table->string('phone',20)->nullable();
            $table->string('email',500)->nullable();
            $table->string('image',80)->nullable();
            $table->text('obs')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('adresses', function(Blueprint $table){
            $table->increments('id');
            $table->integer('person_id');
            $table->string('street',350);
            $table->string('number',10);
            $table->string('complement',80);
            $table->string('city',80);
            $table->string('state',80);
            $table->string('country',80);
            $table->string('zip',20);
            $table->boolean('principal')->default(true);
            $table->timestamps();
        });

        Schema::create('posts', function(Blueprint $table){
            $table->increments('id');
            $table->string('post_name');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('departments', function(Blueprint $table){
            $table->increments('id');
            $table->string('department_name');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        
        Schema::create('employees', function(Blueprint $table){
            $table->integer('person_id')->unique();
            $table->decimal('salary',10,2)->nullable();
            $table->date('hiring_date')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('post_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('permission');
            $table->text('list');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',20);
            $table->string('last_name', 150);
            $table->string('phone')->nullable();
            $table->string('email',150)->unique();
            $table->string('password');
            $table->integer('permission_id');
            $table->integer('person_id');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand_name', 150);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('services', function(Blueprint $table){
            $table->increments('id');
            $table->string('service_name',300);
            $table->decimal('price',10,2);
            $table->boolean('active')->default(true);
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
        $tables = ['adresses','employees','departments','posts','users','permissions','brands','services','persons'];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
        
    }
}
