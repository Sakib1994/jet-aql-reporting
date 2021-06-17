<?php

namespace App\Http\Controllers;

use App\Models\adsAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class AdsAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Account/Index',[
            'accounts'=>adsAccount::latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Account/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:150|alpha_dash',
            'aqlName' => 'required|max:150|alpha_dash',
            'accountId' => "required|integer",
            'platform' => "required|alpha_dash",
            'monthlyBudget' => "required|integer",
            'dailyBudget' => "required|integer",
        ]);
        $account = new AdsAccount([
            'name' => $request->name,
            'aqlName' => $request->aqlName,
            'accountId' => $request->accountId,
            'platform' => $request->platform,
            'monthlyBudget' => $request->monthlyBudget,
            'dailyBudget' => $request->dailyBudget,
        ]);
        $account->save();
        return Redirect::route('ads-accounts.index')->with('success', 'New account created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\adsAccount  $adsAccount
     * @return \Illuminate\Http\Response
     */
    public function show(adsAccount $adsAccount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\adsAccount  $adsAccount
     * @return \Illuminate\Http\Response
     */
    public function edit(adsAccount $adsAccount)
    {
        // dd($adsAccount);
        return Inertia::render('Account/Update', [
            'account' => [
                'id' => $adsAccount->id,
                'name' => $adsAccount->name,
                'aqlName' => $adsAccount->aqlName,
                'platform' => $adsAccount->platform,
                'accountId' => $adsAccount->accountId,
                'dailyBudget' => $adsAccount->dailyBudget,
                'monthlyBudget' => $adsAccount->monthlyBudget,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\adsAccount  $adsAccount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, adsAccount $adsAccount)
    {
        if (AdsAccount::find($adsAccount)) {
            $adsAccount->update($request->all());
        }
        return Redirect::route('ads-accounts.index')->with('success', 'Account updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\adsAccount  $adsAccount
     * @return \Illuminate\Http\Response
     */
    public function destroy(adsAccount $adsAccount)
    {
        $adsAccount->delete();
        return Redirect::route('ads-accounts.index')->with('success', 'Account deleted successfully.');
    }
}
