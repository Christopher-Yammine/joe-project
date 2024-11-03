<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class CreateDemographicsView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(" 
            CREATE VIEW hourly_demographics_view AS 
            SELECT 
                e.stream_id,            
                d.id AS demographic_id,
                m.name AS metric,         
                p.name AS person_type,     
                g.gender AS gender,
                a.group_name AS age_group,
                s.sentiment AS sentiment,
                e.date,
                SUM(e.value) AS value      
            FROM 
                demographics d 
            JOIN 
                genders g ON d.gender_id = g.id 
            JOIN 
                age_groups a ON d.age_group_id = a.id 
            JOIN 
                sentiments s ON d.sentiment_id = s.id 
            JOIN 
                etl_data_hourly e ON d.id = e.demographics_id 
            JOIN 
                metrics m ON e.metric_id = m.id   
            JOIN 
                person_types p ON e.person_type_id = p.id 
            GROUP BY 
                e.stream_id, d.id, m.name, p.name, g.gender, a.group_name, s.sentiment, e.date
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS hourly_demographics_view");
    }
}
