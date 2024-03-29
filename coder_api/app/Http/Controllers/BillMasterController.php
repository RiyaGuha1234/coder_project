<?php

namespace App\Http\Controllers;

use App\Models\BillDetails;
use App\Models\BillMaster;
use App\Models\CustomVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function saveBill(Request $request)
    {
        $billData = $request['billInfo'];
//        DB::transaction();
        $temp_date = explode('-', $billData[0]['date']) ;
        $accounting_year = '';
        if ($temp_date[1] > 3) {
            $x = $temp_date[0] % 100;
            $accounting_year = $x * 100 + ($x + 1);
        } else {
            $x = $temp_date[0] % 100;
            $accounting_year = ($x - 1) * 100 + $x;
        }
        $customVoucher = CustomVoucher::where('accounting_year',$accounting_year)->first();
        if($customVoucher){
            $customVoucher->last_counter =   $customVoucher->last_counter +1;
            $customVoucher->save();
        }
        else{
            $customVoucher =  new CustomVoucher();
            $customVoucher->voucher_name = 'bill';
            $customVoucher->last_counter = 1;
            $customVoucher->accounting_year =  $accounting_year;
            $customVoucher->delimiter = '-';
            $customVoucher->prefix = 'BILL';
            $customVoucher->suffix = 'COD';
            $customVoucher->save();
        }
        try{
            if($customVoucher) {
                $billMaster = new BillMaster();
                $billMaster->bill_number = $customVoucher->prefix
                    . $customVoucher->delimiter
                    . $customVoucher->suffix
                    . $customVoucher->delimiter
                    . str_pad($customVoucher->last_counter, 5, 0,STR_PAD_LEFT)
                    . $customVoucher->delimiter
                    . $customVoucher->accounting_year;
                $billMaster->student_id = $billData[0]['student_id'];
                $billMaster->bill_date = $billData[0]['date'];
                $billMaster->save();
                if ($billMaster) {
                    foreach ($billData as $item) {
                        $billdetails = new BillDetails();
                        $billdetails->bill_master_id = $billMaster->id;
                        $billdetails->course_id = $item['course_id'];
                        $billdetails->paid = $item['fees_paid'];
                        $billdetails->due = $item['due'];
                        $billdetails->save();
                    }

                }
            }
//            DB::commit();
        }
        catch (\Exception $e) {
//                DB::rollBack();
                return response()->json(['Success' => 1, 'Exception' => $e], 401);
        }

        return  response()->json(['success'=>1,'data'=>$billMaster],200,[],JSON_NUMERIC_CHECK);


    }

    public function getBilledStudents(){
        $data =  BillMaster::select('students.student_name','bill_masters.student_id')
                 ->join('students','students.id','=','bill_masters.student_id')
                 ->groupBy('bill_masters.student_id','students.student_name')
                 ->get();

        return  response()->json(['success'=>100,'data'=>$data],200,[],JSON_NUMERIC_CHECK);
    }

    public  function getBillDetails($id){
        $data = BillMaster::select()
                            ->where('student_id',$id)
                            ->get();

        return  response()->json(['success'=>100,'data'=>$data],200,[],JSON_NUMERIC_CHECK);
    }


    public  function  getBill($id){
        $data = BillMaster::select('bill_masters.id','bill_masters.bill_number','bill_masters.student_id','bill_masters.bill_date','bill_details.course_id','bill_details.paid','bill_details.due','courses.course_name','course_types.type','students.student_name')
                ->join('bill_details','bill_masters.id','=','bill_details.bill_master_id')
                ->join('courses','courses.id','=','bill_details.course_id')
                ->join('course_types','course_types.id','=','courses.course_type_id')
                ->join('students','students.id','=','bill_masters.student_id')
                ->where('bill_masters.id' , $id)
                ->get();

        return  response()->json(['success'=>100,'data'=>$data],200,[],JSON_NUMERIC_CHECK);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BillMaster  $billMaster
     * @return \Illuminate\Http\Response
     */
    public function show(BillMaster $billMaster)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BillMaster  $billMaster
     * @return \Illuminate\Http\Response
     */
    public function edit(BillMaster $billMaster)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BillMaster  $billMaster
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BillMaster $billMaster)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BillMaster  $billMaster
     * @return \Illuminate\Http\Response
     */
    public function destroy(BillMaster $billMaster)
    {
        //
    }
}
