<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Owner;
use App\Trademark;
use App\Jobs\FreeSearchReport;
use Illuminate\Http\Request;

class TrademarkController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return Trademark
     */
    public function store(Request $request)
    {
        $req = $request->validate([
            'mark_desc' => 'required',
            'mark_type' => 'required',
            'needs_translation' => 'required|boolean',
            'teas_plus' => 'required|boolean',
            'translation' => 'nullable',
            'protect_all_colors' => 'required|boolean',
            'includes_living_name' => 'required|boolean',
            'own_name' => 'required|boolean',
            'other_living_name' => 'required|boolean',
            'package' => 'required',
            'search_type' => 'required',
            'in_use' => 'required|boolean',
            'first_use_anywhere' => 'nullable|date',
            'first_use_commerce' => 'nullable|date'
        ]);

        $data = $request->all();

        $trademark = new Trademark([
            'user_id' => $request->user()->id,
            'mark' => $req['mark_desc'],
            'type' => $req['mark_type'],
            'teas_plus' => $req['teas_plus'],
            'literal_elements' => $req['mark_type'] == 'Logo'
                ? ($data['literal_elements'] ?? '')
                : '',
            'needs_translation' => $req['needs_translation'],
            'translation' => $req['translation'] ?? '',
            'protect_all_colors' => $req['protect_all_colors'],
            'colors_in_logo' => isset($data['colors_in_logo'])
                ? $data['colors_in_logo']
                : null,
            'includes_living_name' => $req['includes_living_name'],
            'own_name' => $req['own_name'],
            'own_name_name' => isset($data['own_name_name'])
                ? $data['own_name_name']
                : null,
            'other_living_name' => $req['other_living_name'],
            'package' => $req['package'],
            'search_type' => $req['search_type'],
            'in_use' => $req['in_use'],
            'first_use_anywhere' =>
                $req['first_use_anywhere'] ?? new Carbon('1900-01-01'),
            'first_use_commerce' =>
                $req['first_use_commerce'] ?? new Carbon('1900-01-01')
        ]);

        $trademark->save();

        if ($req['search_type'] == 'federal') {
            FreeSearchReport::dispatch($trademark)->delay(now()->addDays(3));
        }

        return $trademark;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Trademark $trademark
     * @param Request $request
     * @return Trademark
     */
    public function show(Trademark $trademark, Request $request)
    {
        if ($request->user()->cant('view', $trademark)) {
            abort(401);
        }
        return $trademark;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Trademark $trademark
     * @return Trademark
     */
    public function update(Request $request, Trademark $trademark)
    {
        if ($request->user()->cant('update', $trademark)) {
            abort(401);
        }

        $data = $request->validate([
            'field' => 'required|string',
            'value' => 'required'
        ]);

        $field = $data['field'];

        $trademark->$field = $data['value'];

        $trademark->save();

        return $trademark;
    }

    /**
     * Link An Owner Model to a Trademark
     * @param Trademark $trademark
     * @param \App\Owner $owner
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function linkOwner(
        Trademark $trademark,
        Owner $owner,
        Request $request
    ) {
        if ($request->user()->cant('update', $trademark)) {
            abort(401);
        }

        $trademark->owners()->save($owner);

        return response(200);
    }
}
