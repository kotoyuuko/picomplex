<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'file' => 'required|max:5096|image'
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $file = $request->file;

        try {
            $url = $this->uploadToSMMS($file)['data']['url'];
            return $this->success($url);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Upload image to sm.ms
     *
     * @param  UploadedFile $file
     * @return array
     */
    private function uploadToSMMS($file)
    {
        $client = new Client();

        $response = $client->post('https://sm.ms/api/upload', [
            'multipart' => [
                [
                    'name' => 'smfile',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ]
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function success($data = [], $code = 200)
    {
        return ['code' => $code, 'data' => $data];
    }
    public function fail($data = [], $code = 500)
    {
        return ['code' => $code, 'data' => $data];
    }
}
