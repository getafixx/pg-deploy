<?php

namespace App\Http\Controllers;

use App\Services\SimpleCryptService;
use Illuminate\Support\Facades\Artisan;

class DeployController extends Controller
{
    /**
     * Show the Deployer Reference
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function deployerRef()
    {
        $file = storage_path(ENV('DEPLOYER_REF'));
        if (file_exists($file)) {
            $deployTxt = SimpleCryptService::decode(file_get_contents($file));
            return $deployTxt;
        }

        return response()->json(['deployment information file not found - sorry'], 404);
    }

    public function deployerTest()
    {      
        //'localhost',
       /* $exitCode = $this->call('deploy ', [
                'localhost', '--file' => base_path('deploy/deploy.php')
            ]);*/
        $exitCode = \Artisan::call('deploy', [
                '--file' => base_path('deploy/deploy.php', 'paint.garden.deploytest')
            ]);
        
        return response()->json(['result' => $exitCode], 200);
    }

    //
    //
}
