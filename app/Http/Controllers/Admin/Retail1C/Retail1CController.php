<?php

namespace App\Http\Controllers\Admin\Retail1C;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Retail1C\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class Retail1CController extends Controller
{
    public function index(Request $request){
        Log::info($request);
        if (($request['type'] === 'catalog' || $request['type'] === 'sale') && $request['mode'] === 'checkauth'){
            $phone = $request->header('Php-Auth-User');
            $password = $request->header('Php-Auth-Pw');
            $user = User::where('phone', $phone)->first();
            if (Auth::attempt(['phone' => $phone, 'password' => $password])
                && $user && $user->position_id === 3){
                return "success\n1C-Cookie\nPm6@TMY@J(%V";
            };
            return 'Авторизация не удалась';
        }
        if (($request['type'] === 'catalog' || $request['type'] === 'sale') && $request['mode'] === 'init') {
            if ($request->header('Cookie') === '1C-Cookie=Pm6@TMY@J(%V'){
                return "zip=yes\nfile_limit=512000";
            }
            return 'Неверное значение Cookie';
        }
    }

    public function upload_files(Request $request, Service $service){
        Log::info($request);
        $upload_dir = '/var/www/uploads/';
        $post_data = file_get_contents("php://input");
        $filename = $upload_dir.$request['filename'];
        file_put_contents($filename, $post_data);
        $zip = new ZipArchive();
        if (explode('.', $request['filename'])[1] === 'zip' && $zip->open($filename) === TRUE) {
            $zip->extractTo($upload_dir.'unzip/');
            $zip->close();
            unlink($filename);
            $import_xml = simplexml_load_file($upload_dir.'unzip/import0_1.xml');
            //$offers_xml = simplexml_load_file($upload_dir.'unzip/offers.xml');
            //if ($import_xml && $offers_xml) {
            //    $service->store($import_xml, $offers_xml);
            //}
            return 'success';
        }
    }
}
