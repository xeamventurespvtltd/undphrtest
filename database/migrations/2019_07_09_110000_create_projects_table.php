<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');

            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('approval_status', ['0', '1'])->comment('0=Not Approved, 1=Approved');
            $table->string('address')->nullable();
            $table->integer('number_of_resources');
            $table->enum('type', ['1', '2', '3'])->comment('1=Government, 2=Corporate, 3=International');
            $table->string('tenure_years')->default('1');
            $table->string('tenure_months')->default('0');

            $table->unsignedBigInteger('salary_structure_id');
            $table->foreign('salary_structure_id')->references('id')->on('salary_structures')->onDelete('cascade');

            $table->unsignedBigInteger('salary_cycle_id');
            $table->foreign('salary_cycle_id')->references('id')->on('salary_cycles')->onDelete('cascade');

            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->boolean('isactive')->default(1);
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
        Schema::dropIfExists('projects');
    }
}
