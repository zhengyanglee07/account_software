<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class TableController extends Controller
{
	//
	public function showTable($tableName){

		$tableColumn = $this->getTableColumn($tableName);
		// dd(json_encode($tableColumn));
		$tableData = DB::table($tableName)->get();
		// dd($tableData);
		// dd($tableName);
		return Inertia::render('master-admin/pages/TableComponent', compact('tableName','tableData','tableColumn'));
		// $tableData = '123123123';
		// dd($name);
		// return view('masterAdmin.table', compact('tableData', 'tableName', 'tableColumn'));


	}

	public function showAddRecordForm($tableName){
		$tableColumn = $this->getTableColumn($tableName);
		return Inertia::render('master-admin/pages/AddRecordFormComponent', compact('tableName','tableColumn'));
	}

	public function addRecord($tableName, Request $request){
		try{
			$dataObject = json_decode($request->dataObject);
			$queryArray = (array)$dataObject;

			if (array_key_exists('password', $queryArray)) {
				$queryArray['password'] = Hash::make($queryArray['password']);
			}

			$queryArray['created_at'] = \Carbon\Carbon::now();
			$queryArray['updated_at'] = \Carbon\Carbon::now();

			DB::table($tableName)->insert($queryArray);

			return response()->json(['status' => 'success']);

		} catch(Exception $e){
			// dd($e);
			return response()->json(['status' => 'error']);
		}

	}

	public function showEditRecordForm($tableName, $recordID){
		$tableColumn = $this->getTableColumn($tableName);
		$recordData = DB::table($tableName)->where('id', $recordID)->get(); // change here for subplan permission
		return Inertia::render('master-admin/pages/EditRecordFormComponent', compact('tableName','tableColumn','recordData'));
	}

	public function editRecord($tableName, $recordID, Request $request){
		$tableColumn = $this->getTableColumn($tableName);
		try{
			$dataObject = json_decode($request->dataObject);
			$queryArray = (array)$dataObject;
			if (array_key_exists('password', $queryArray) && !\DB::table($tableName)->where('password', $queryArray['password'])->exists()) {
				$queryArray['password'] = Hash::make($queryArray['password']);
			}

			$queryArray['updated_at'] = \Carbon\Carbon::now();

			DB::table($tableName)->where('id', $recordID)->update($queryArray); // change here for subplan permission

			return response()->json(['status' => 'success']);

		} catch(Exception $e){
			// dd($e);
			return response()->json(['status' => 'error']);
		}

	}

	private function getTableColumn($tableColumn){
		return DB::getSchemaBuilder()->getColumnListing($tableColumn);
	}


	/*
		- The function doesnt accept some table name e.g. csv_data, as it will return CsvDatum for some unknown reasons.
		- Will make adjustment for this function to prevent mass assignment for some fields.
	*/
	// private function getModelByTablename($tableName) {
	// 	return \Str::studly(strtolower(\Str::singular($tableName)));
	// }





}
