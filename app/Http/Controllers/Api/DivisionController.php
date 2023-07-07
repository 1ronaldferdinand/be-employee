<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Models\DivisionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DivisionController extends Controller
{
    public function index(Request $request)
    {
        $divisions = DivisionModel::where('status', '!=', 0)->orderby('name', 'asc')->get();
        $response = ApiFormatter::createJson(200, 'Get Data Success', $divisions);
        return response()->json($response);
    }

    public function store(Request $request)
    {
        try {
            $params = $request->all();

            $validator = Validator::make($params, 
                [
                    'name' => 'required',
                ],
                [
                    'name.required' => 'Division\'s name is required',
                ]
            );

            if ($validator->fails()) {
                $response = ApiFormatter::createJson(400, 'Bad Request', $validator->errors()->all());
                return response()->json($response);
            }

            $division = [
                'name'        => $params['name'],
                'description' => $params['description']?? null,
                'status'      => $params['status']?? 1,
            ];

            $data = DivisionModel::create($division);

            $response = ApiFormatter::createJson(200, 'Create division success', $data);
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
            return response()->json($response);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $params = $request->all();

            $validator = Validator::make($params, 
                [
                    'name' => 'required',
                ],
                [
                    'name.required' => 'Division\'s name is required',
                ]
            );

            if ($validator->fails()) {
                $response = ApiFormatter::createJson(400, 'Bad Request', $validator->errors()->all());
                return response()->json($response);
            }

            $prevDivision = $this->getDivision($id);
            if(is_null($prevDivision)){
                return ApiFormatter::createJson(404, 'Data not found');
            }

            $division = [
                'name'        => $params['name'],
                'description' => $params['description']?? null,
                'status'      => $params['status']?? 1,
            ];

            $prevDivision->update($division);
            $updatedDivision = $prevDivision->fresh();

            $response = ApiFormatter::createJson(200, 'Update division success', $updatedDivision);
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
            return response()->json($response);
        }
    }

    public function delete($id)
    {
        try {
            $division = $this->getDivision($id);
            
            if(is_null($division)){
                return ApiFormatter::createJson(404, 'Data not found');
            }

            $division->status = 0;
            $division->save();

            $response = ApiFormatter::createJson(200, 'Delete division success');
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
            return response()->json($response);
        }
    }

    public function show($id)
    {
        try {
            $division = $this->getDivision($id);
            if(is_null($division)){
                return ApiFormatter::createJson(404, 'division not found');
            }
            $response = ApiFormatter::createJson(200, 'Get detail division sucess', $division);
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ApiFormatter::createJson(400, $e->getMessage());
            return response()->json($response);
        }
    }

    public function getDivision($id){
        $division = DivisionModel::where('id', $id)
                                    ->where('status', '!=', 0)
                                    ->first();
        return $division;
    }
}
