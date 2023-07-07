<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Models\PositionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $positions = PositionModel::where('status', '!=', 0)->orderby('name', 'asc')->get();
        $response = ApiFormatter::createJson(200, 'Get Data Success', $positions);
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
                    'name.required' => 'Position\'s name is required',
                ]
            );

            if ($validator->fails()) {
                $response = ApiFormatter::createJson(400, 'Bad Request', $validator->errors()->all());
                return response()->json($response);
            }

            $position = [
                'name'        => $params['name'],
                'description' => $params['description']?? null,
                'status'      => $params['status']?? 1,
            ];

            $data = PositionModel::create($position);
            $response = ApiFormatter::createJson(200, 'Create position success', $data);
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
                    'name.required' => 'Position\'s name is required',
                ]
            );

            if ($validator->fails()) {
                $response = ApiFormatter::createJson(400, 'Bad Request', $validator->errors()->all());
                return response()->json($response);
            }

            $prevPosition = $this->getPosition($id);
            if(is_null($prevPosition)){
                return ApiFormatter::createJson(404, 'Data not found');
            }

            $position = [
                'name'        => $params['name'],
                'description' => $params['description']?? null,
                'status'      => $params['status']?? 1,
            ];

            $prevPosition->update($position);
            $updatedPosition = $prevPosition->fresh();

            $response = ApiFormatter::createJson(200, 'Update position success', $updatedPosition);
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
            return response()->json($response);
        }
    }

    public function delete($id)
    {
        try {
            $position = $this->getPosition($id);
            
            if(is_null($position)){
                return ApiFormatter::createJson(404, 'Data not found');
            }

            $position->status = 0;
            $position->save();

            $response = ApiFormatter::createJson(200, 'Delete position success');
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
            return response()->json($response);
        }
    }

    public function show($id)
    {
        try {
            $position = $this->getPosition($id);
            if(is_null($position)){
                return ApiFormatter::createJson(404, 'position not found');
            }
            $response = ApiFormatter::createJson(200, 'Get detail position sucess', $position);
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ApiFormatter::createJson(400, $e->getMessage());
            return response()->json($response);
        }
    }

    public function getPosition($id){
        $position = PositionModel::where('id', $id)
                                    ->where('status', '!=', 0)
                                    ->first();
        return $position;
    }
}
