<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfitToVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('cost')->unsigned()->nullable();
            $table->integer('import_tax')->unsigned()->nullable();
            $table->integer('inward_shipping_cost')->unsigned()->nullable();
            $table->integer('margin')->unsigned()->nullable();
            $table->integer('profit')->unsigned()->nullable();
            $table->dateTime('replenishment_arrival_date')->nullable();
            $table->string('replenishment_units')->nullable();
        });
    }

    public function down()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('cost');
            $table->dropColumn('import_tax');
            $table->dropColumn('inward_shipping_cost');
            $table->dropColumn('margin');
            $table->dropColumn('profit');
            $table->dropColumn('replenishment_arrival_date');
            $table->dropColumn('replenishment_units');
        });
    }
}
