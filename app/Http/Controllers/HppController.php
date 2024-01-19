<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\HppRequest;
use App\Models\Hpp;

class HppController extends Controller
{
    public function index()
    {
        $hpp = Hpp::all();
        if ($hpp->count() > 0) {
            return response()->json([
                'status' => 200,
                'hpp' => $hpp
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Data Not Found'
            ], 404);
        }
    }

    public function create(HppRequest $request)
    {
        $previousHPP = Hpp::latest('id')->value('hpp');
        $cost = $request->description == 'Pembelian' ? $request->price : $previousHPP;
        $qty = $request->description == 'Pembelian' ? $request->qty : -($request->qty);
        $total_cost = $qty * $cost;

        $previousQty = Hpp::latest('id')->value('qty_balance');
        $qty_balance = $previousQty + $qty;

        $previousValue = Hpp::latest('id')->value('value_balance');
        $value_balance = $previousValue + $total_cost;

        // $nilai_hpp = $value_balance/$qty_balance;
        $nilai_hpp = ($qty_balance > 0) ? $value_balance / $qty_balance : 0;
        

        $hpp = Hpp::create([
            'description' => $request->description,
            'qty' => $qty,
            'date' => $request->date,
            'price' => $request->price,
            'cost' => $cost,
            'total_cost' => $total_cost,
            'qty_balance' => $qty_balance,
            'value_balance' => $value_balance,
            'hpp' => $nilai_hpp
        ]);

        if ($hpp) {
            return response()->json(['message' => 'Data Berhasil Disimpan']);
        } else {
            return response()->json(['message' => 'Data Gagal Disimpan']);
        }
        
    }

    public function update(HppRequest $request, $id)
    {
        $hpp = Hpp::findOrFail($id);
        
        if ($hpp) {

            $previousHPP = Hpp::where('id' ,'<', $id)->value('hpp');
            $cost = $request->description == 'Pembelian' ? $request->price : $previousHPP;
            $qty = $request->description == 'Pembelian' ? $request->qty : -($request->qty);
            $total_cost = $qty * $cost;

            $previousQty = Hpp::where('id' ,'<', $id)->value('qty_balance');
            $qty_balance = $previousQty + $qty;

            $previousValue = Hpp::where('id' ,'<', $id)->value('value_balance');
            $value_balance = $previousValue + $total_cost;

            $nilai_hpp = $value_balance/$qty_balance;


            // Update data lainnya
            Hpp::where('id', '>', $id)->update([
                'qty_balance' => \DB::raw("$qty_balance + qty"),
                'value_balance' => \DB::raw("total_cost + $value_balance"),
                'hpp' => \DB::raw("value_balance / qty_balance")
            ]);

            $hpp->update([
                'description' => $request->description,
                'qty' => $qty,
                'date' => $request->date,
                'price' => $request->price,
                'cost' => $cost,
                'total_cost' => $total_cost,
                'qty_balance' => $qty_balance,
                'value_balance' => $value_balance,
                'hpp' => $nilai_hpp
            ]);
            return response()->json(['message' => 'Data Berhasil Diubah']);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Data Not Found'
            ], 404);
        }
        
    }

    public function destroy($id)
    {
        $hpp = Hpp::findOrFail($id);
        // $sum = Hpp::sum('qty');

        if ($hpp) {
                $hpp->delete();
                return response()->json(['message' => 'Data Berhasil Dihapus']);
            
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Data Not Found'
            ], 404);
        }
    }
}
