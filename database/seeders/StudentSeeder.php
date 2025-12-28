<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\HallAttachment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Constants\Constants;


class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('students')->truncate();
        DB::table('hall_attachments')->truncate();

        // ===== Full 40 students data =====
        $studentsData = [
            // Male Students 1–20
            ['name'=>'Tanvir Alam','email'=>'tanviralam@example.com','mobile_number'=>'01710000001','department_id'=>1,'roll'=>'21102001','registration'=>'9879','hall_id'=>1],
            ['name'=>'Shagor Ahmed','email'=>'shagorahmed@example.com','mobile_number'=>'01710000002','department_id'=>2,'roll'=>'21102901','registration'=>'9880','hall_id'=>3],
            ['name'=>'Mehedi Hasan','email'=>'mehedihasan@example.com','mobile_number'=>'01710000003','department_id'=>3,'roll'=>'21103401','registration'=>'9881','hall_id'=>1],
            ['name'=>'Rafiq Islam','email'=>'rafqislam@example.com','mobile_number'=>'01710000004','department_id'=>4,'roll'=>'21104001','registration'=>'9882','hall_id'=>3],
            ['name'=>'Sabbir Hossain','email'=>'sabbirhossain@example.com','mobile_number'=>'01710000005','department_id'=>5,'roll'=>'21112101','registration'=>'9883','hall_id'=>1],
            ['name'=>'Rakib Hasan','email'=>'rakibhasan@example.com','mobile_number'=>'01710000006','department_id'=>6,'roll'=>'21112201','registration'=>'9884','hall_id'=>3],
            ['name'=>'Jamil Khan','email'=>'jamilkhan@example.com','mobile_number'=>'01710000007','department_id'=>7,'roll'=>'21112301','registration'=>'9885','hall_id'=>1],
            ['name'=>'Shamim Reza','email'=>'shamimreza@example.com','mobile_number'=>'01710000008','department_id'=>8,'roll'=>'21112701','registration'=>'9886','hall_id'=>3],
            ['name'=>'Naimul Hasan','email'=>'naimulhasan@example.com','mobile_number'=>'01710000009','department_id'=>9,'roll'=>'21112801','registration'=>'9887','hall_id'=>1],
            ['name'=>'Fahim Khan','email'=>'fahimkhan@example.com','mobile_number'=>'01710000010','department_id'=>10,'roll'=>'21113301','registration'=>'9888','hall_id'=>3],
            ['name'=>'Arif Hossain','email'=>'arifhossain@example.com','mobile_number'=>'01710000011','department_id'=>11,'roll'=>'21113901','registration'=>'9889','hall_id'=>1],
            ['name'=>'Kamrul Hasan','email'=>'kamrulhasan@example.com','mobile_number'=>'01710000012','department_id'=>12,'roll'=>'21114401','registration'=>'9890','hall_id'=>3],
            ['name'=>'Anik Roy','email'=>'anikroy@example.com','mobile_number'=>'01710000013','department_id'=>13,'roll'=>'21122401','registration'=>'9891','hall_id'=>1],
            ['name'=>'Sajid Hossain','email'=>'sajidhossain@example.com','mobile_number'=>'01710000014','department_id'=>14,'roll'=>'21123101','registration'=>'9892','hall_id'=>3],
            ['name'=>'Tuhin Ahmed','email'=>'tuhinahmed@example.com','mobile_number'=>'01710000015','department_id'=>15,'roll'=>'21123201','registration'=>'9893','hall_id'=>1],
            ['name'=>'Asif Reza','email'=>'asifreza@example.com','mobile_number'=>'01710000016','department_id'=>16,'roll'=>'21123601','registration'=>'9894','hall_id'=>3],
            ['name'=>'Imran Khan','email'=>'imrankhan@example.com','mobile_number'=>'01710000017','department_id'=>17,'roll'=>'21123701','registration'=>'9895','hall_id'=>1],
            ['name'=>'Tanvir Hossain','email'=>'tanvirhossain@example.com','mobile_number'=>'01710000018','department_id'=>18,'roll'=>'21123801','registration'=>'9896','hall_id'=>3],
            ['name'=>'Mahmud Alam','email'=>'mahmudalam@example.com','mobile_number'=>'01710000019','department_id'=>19,'roll'=>'21124201','registration'=>'9897','hall_id'=>1],
            ['name'=>'Samir Hasan','email'=>'samirhasan@example.com','mobile_number'=>'01710000020','department_id'=>20,'roll'=>'21132501','registration'=>'9898','hall_id'=>3],

            // Female Students 1–20
            ['name'=>'Sumaiya Akter','email'=>'sumaiyaakter@example.com','mobile_number'=>'01710000021','department_id'=>1,'roll'=>'21102005','registration'=>'9899','hall_id'=>2],
            ['name'=>'Fatema Begum','email'=>'fatemabegum@example.com','mobile_number'=>'01710000022','department_id'=>2,'roll'=>'21102902','registration'=>'9900','hall_id'=>4],
            ['name'=>'Nurjahan Begum','email'=>'nurjahanbegum@example.com','mobile_number'=>'01710000023','department_id'=>3,'roll'=>'21103402','registration'=>'9901','hall_id'=>2],
            ['name'=>'Jahanara Begum','email'=>'jahanarabegum@example.com','mobile_number'=>'01710000024','department_id'=>4,'roll'=>'21104002','registration'=>'9902','hall_id'=>4],
            ['name'=>'Rokeya Begum','email'=>'rokeyabegum@example.com','mobile_number'=>'01710000025','department_id'=>5,'roll'=>'21112102','registration'=>'9903','hall_id'=>2],
            ['name'=>'Momena Khatun','email'=>'momenakhatun@example.com','mobile_number'=>'01710000026','department_id'=>6,'roll'=>'21112202','registration'=>'9904','hall_id'=>4],
            ['name'=>'Mst. Halima','email'=>'msthalima@example.com','mobile_number'=>'01710000027','department_id'=>7,'roll'=>'21112302','registration'=>'9905','hall_id'=>2],
            ['name'=>'Julekha Begum','email'=>'julekhabegum@example.com','mobile_number'=>'01710000028','department_id'=>8,'roll'=>'21112702','registration'=>'9906','hall_id'=>4],
            ['name'=>'Mst. Rina','email'=>'mstrina@example.com','mobile_number'=>'01710000029','department_id'=>9,'roll'=>'21112802','registration'=>'9907','hall_id'=>2],
            ['name'=>'Mst. Asia','email'=>'mstasia@example.com','mobile_number'=>'01710000030','department_id'=>10,'roll'=>'21113302','registration'=>'9908','hall_id'=>4],
            ['name'=>'Mst. Salma','email'=>'mstsalma@example.com','mobile_number'=>'01710000031','department_id'=>11,'roll'=>'21113902','registration'=>'9909','hall_id'=>2],
            ['name'=>'Anika Khanam','email'=>'anikakhanam@example.com','mobile_number'=>'01710000032','department_id'=>12,'roll'=>'21114402','registration'=>'9910','hall_id'=>4],
            ['name'=>'Farhana Akter','email'=>'farhanaakter@example.com','mobile_number'=>'01710000033','department_id'=>13,'roll'=>'21122402','registration'=>'9911','hall_id'=>2],
            ['name'=>'Tasnim Begum','email'=>'tasnimbegum@example.com','mobile_number'=>'01710000034','department_id'=>14,'roll'=>'21123102','registration'=>'9912','hall_id'=>4],
            ['name'=>'Shirin Akter','email'=>'shirinakter@example.com','mobile_number'=>'01710000035','department_id'=>15,'roll'=>'21123202','registration'=>'9913','hall_id'=>2],
            ['name'=>'Nabila Islam','email'=>'nabila@example.com','mobile_number'=>'01710000036','department_id'=>16,'roll'=>'21123602','registration'=>'9914','hall_id'=>4],
            ['name'=>'Sabina Akter','email'=>'sabinaakter@example.com','mobile_number'=>'01710000037','department_id'=>17,'roll'=>'21123702','registration'=>'9915','hall_id'=>2],
            ['name'=>'Laila Begum','email'=>'lailabegum@example.com','mobile_number'=>'01710000038','department_id'=>18,'roll'=>'21123802','registration'=>'9916','hall_id'=>4],
            ['name'=>'Rupa Khanam','email'=>'rupakhanam@example.com','mobile_number'=>'01710000039','department_id'=>19,'roll'=>'21124202','registration'=>'9917','hall_id'=>2],
            ['name'=>'Mim Akter','email'=>'mimakter@example.com','mobile_number'=>'01710000040','department_id'=>20,'roll'=>'21132502','registration'=>'9918','hall_id'=>4],
        ];

         // 2. Get last user id
        $lastUser = DB::table('users')->latest('id')->first();
        $nextId = $lastUser ? $lastUser->id + 1 : 1;

        foreach ($studentsData as $data) {
            // Insert into users table
            $user = User::factory()->create([
                'id' => $nextId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('12345'),
                'is_active' => 1,
            ]);
            $user->assignRole(Constants::ROLE_STUDENT);

            // Insert into students table
            $student = Student::create([
                'user_id' => $nextId,
                'name' => $data['name'],
                'department_id' => $data['department_id'],
                'roll' => $data['roll'],
                'registration' => $data['registration'],
                'mobile_number' => $data['mobile_number'],
                'hall_id' => $data['hall_id'],
                'hall_status' => 'attachment',
                'is_active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Insert into hall_attachments
            HallAttachment::create([
                'hall_id' => $data['hall_id'],
                'student_id' => $student->id,
                'is_approved' => 1,
                'is_active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $nextId++;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
