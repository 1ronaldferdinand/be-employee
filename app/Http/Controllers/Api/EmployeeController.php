<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Models\EmployeeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = EmployeeModel::where('status', '!=', 0)
                                    ->with(['position', 'division'])
                                    ->orderby('name', 'asc')
                                    ->get();
        
        $response = ApiFormatter::createJson(200, 'Get employees success', $employees);
        return response()->json($response);
    }

    public function store(Request $request)
    {
        try {
            $params = $request->all();

            $validator = Validator::make($params, 
                [
                    'division_id' => 'required | exists:divisions,id',
                    'position_id' => 'required | exists:positions,id',
                    'name'        => 'required',
                    'code'        => 'required',
                    'gender'      => 'required',
                    'phone'       => 'required',
                    'birthdate'   => 'required',
                    'email'       => 'required | email | unique:employees,email',
                ], 
                [
                    'position_id.required' => 'Position is required.',
                    'position_id.exists'   => 'The selected position is invalid.',
                    'division_id.required' => 'Division is required.',
                    'division_id.exists'   => 'The selected division is invalid.',
                    'email.email'          => 'Email format is invalid.',
                    'name.required'        => 'Employee\'s name is required',
                    'phone.required'       => 'Employee\'s phone is required',
                    'gender.required'       => 'Employee\'s gender is required',
                    'birthdate.required'   => 'Employee\'s birth is required',
                    'code.required'        => 'Employee\'s code is required'
                ]
            );

            if ($validator->fails()) {
                $response = ApiFormatter::createJson(400, $validator->errors()->first());
                return response()->json($response);
            }

            if (EmployeeModel::where('code', $params['code'])->exists()) {
                $response = ApiFormatter::createJson(400, 'Employee code already exists');
                return response()->json($response);
            }
            
            if ($params['phone']) {
                $containsCountryCode = strpos($params['phone'], '+62') === 0; // Memeriksa apakah nomor telepon dimulai dengan '+62'
                
                if ((!$containsCountryCode && strlen($params['phone']) > 14) 
                    || ($containsCountryCode && strlen($params['phone']) > 16)) {
                    $response = ApiFormatter::createJson(400, 'Phone number exceeds the length');
                    return response()->json($response);
                }
            }            

            $employee = [
                'name'        => $params['name'],
                'division_id' => $params['division_id'],
                'position_id' => $params['position_id'],
                'code'        => $params['code'],
                'gender'      => $params['gender'],
                'phone'       => $params['phone'],
                'email'       => $params['email'],
                'birthdate'   => $params['birthdate'],
                'status'      => $params['status']?? 1,
                'image'       => $this->getImageUrl($request), 
            ];

            $data       = EmployeeModel::create($employee);
            $response   = ApiFormatter::createJson(200, 'Create employee success', $data);
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ApiFormatter::createJson(400, $e->getMessage());
            return response()->json($response);
        }
    }

    public function show($id)
    {
        try {
            $employee = $this->getEmployee($id);
            if(is_null($employee)){
                return ApiFormatter::createJson(404, 'Employee not found');
            }
            $response = ApiFormatter::createJson(200, 'Get detail employee sucess', $employee);
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ApiFormatter::createJson(400, $e->getMessage());
            return response()->json($response);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $prevEmployee = $this->getEmployee($id);
            if(is_null($prevEmployee)){
                return ApiFormatter::createJson(404, 'Employee not found');
            }

            $params = $request->all();
            $validator = Validator::make($params, 
                [
                    'division_id' => 'required | exists:divisions,id',
                    'position_id' => 'required | exists:positions,id',
                    'name'        => 'required',
                    'code'        => 'required',
                    'gender'      => 'required',
                    'phone'       => 'required',
                    'birthdate'   => 'required',
                    'email'       => [
                                        'required',
                                        'email',
                                        Rule::unique('employees', 'email')->ignore($id),
                                    ],
                ], 
                [
                    'position_id.required' => 'Position is required.',
                    'position_id.exists'   => 'The selected position is invalid.',
                    'division_id.required' => 'Division is required.',
                    'division_id.exists'   => 'The selected division is invalid.',
                    'email.email'          => 'Email format is invalid.',
                    'name.required'        => 'Employee\'s name is required',
                    'phone.required'       => 'Employee\'s phone is required',
                    'gender.required'       => 'Employee\'s gender is required',
                    'birthdate.required'   => 'Employee\'s birth is required',
                    'code.required'        => 'Employee\'s code is required'
                ]
            );

            if ($validator->fails()) {
                $response = ApiFormatter::createJson(400, $validator->errors()->first());
                return response()->json($response);
            }

            if (EmployeeModel::where('code', $params['code'])->where('id', '!=', $id)->exists()) {
                $response = ApiFormatter::createJson(400, 'Employee code already exists');
                return response()->json($response);
            }

            if ($params['phone']) {
                $containsCountryCode = strpos($params['phone'], '+62') === 0; // Memeriksa apakah nomor telepon dimulai dengan '+62'
                
                if ((!$containsCountryCode && strlen($params['phone']) > 14) 
                    || ($containsCountryCode && strlen($params['phone']) > 16)) {
                    $response = ApiFormatter::createJson(400, 'Phone number exceeds the length');
                    return response()->json($response);
                }
            }            
            
            $employee = [
                'name'        => $params['name'],
                'division_id' => $params['division_id'],
                'position_id' => $params['position_id'],
                'code'        => $params['code'],
                'gender'      => $params['gender'],
                'phone'       => $params['phone'],
                'email'       => $params['email'],
                'birthdate'   => $params['birthdate'],
                'status'      => $params['status']?? 1,
                'image'       => $request->image? $this->getImageUrl($request) : $prevEmployee->image, 
            ];

            $prevEmployee->update($employee);
            $updatedEmployee = $prevEmployee->fresh();

            $response   = ApiFormatter::createJson(200, 'Update employee success', $updatedEmployee);
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ApiFormatter::createJson(400, $e->getMessage());
            return response()->json($response);
        }
    }

    public function delete($id)
    {
        try {
            $employee = $this->getEmployee($id);
            
            if(is_null($employee)){
                return ApiFormatter::createJson(404, 'Data not found');
            }

            $employee->status = 0;
            $employee->save();

            $response = ApiFormatter::createJson(200, 'Delete employee success');
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ApiFormatter::createJson(500, 'Internal Server Error', $e->getMessage());
            return response()->json($response);
        }
    }

    public function getEmployee($id){
        $employee = EmployeeModel::where('id', $id)
                                ->where('status', '!=', 0)
                                ->with(['position', 'division'])
                                ->first();
        return $employee;
    }

    public function getImageUrl($request){
        $image_name = NULL;
        if ($request->hasFile('image')) {
            $file_dir = public_path('/files/employees/');
            if (!File::exists($file_dir)) {
                File::makeDirectory($file_dir, $mode = 0777, true, true);
            }

            $image = $request->file('image');
            $slug = Str::random(10);
            $image_name = "img_" . $slug . "_" . time() . "." . $image->getClientOriginalExtension();
            $image->move($file_dir, $image_name);

            $host = env('APP_URL');
            $image_name = $host. '/files/employees/' . $image_name;
        }
        return $image_name;
    }
}
