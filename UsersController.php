<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\User;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $req)
    {
        if (
            Auth::user() == User::findOrFail($req->user) ||
            \Gate::allows('crm-login')
        ) {
            return User::findOrFail($req->user);
        } else {
            return 'You do not have access.';
        }
    }

    public function showOrders(Request $req)
    {
        if (Gate::denies('crm-login')) {
            abort(401);
        }
        return User::findOrFail($req->user)
            ->orders()
            ->get();
    }

    public function showShippingAddresses(Request $req)
    {
        if (Gate::denies('crm-login')) {
            abort(401);
        }
        $orders = User::findOrFail($req->user)->orders()->get();
        $shippingAddresses = array();
        foreach ($orders as $order) {
            $bus = $order->business;
            array_push($shippingAddresses, $bus->shippingAddress);
        }
        return $shippingAddresses;
    }

    public function showDocuments(Request $req)
    {
        if (Gate::denies('crm-login')) {
            abort(401);
        }
        $orders = User::findOrFail($req->user)->orders;

        $documents = [];
        foreach ($orders as $order) {
            $orderDocs = [];
            $orderDocs = $order->documents()->get();
            foreach ($orderDocs as $doc) {
                array_push($documents, $doc);
            }
        }
        info($documents);
        return $documents;
    }

    public function showAgentsOrdered(Request $req)
    {
        if (Gate::denies('crm-login')) {
            abort(401);
        }
        return User::findOrFail($req->user)
            ->agents()
            ->get();
    }

    public function getForeignKeys(Request $request)
    {
        return User::findOrFail($request->user)
            ->foreignKeys()
            ->get();
    }

    public function update(Request $req)
    {
        if (Auth::user() == User::findOrFail($req->user)) {
            $this->validate(request(), [
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6|confirmed'
            ]);

            $user = User::find($req->user);

            $user->email = request('email');
            $user->password = bcrypt(request('password'));

            $user->save();

            return back();
        } else {
            return 'You do not have access.';
        }
    }
}
