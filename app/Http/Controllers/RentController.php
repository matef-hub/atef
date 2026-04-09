<?php

namespace App\Http\Controllers;

use App\Http\Requests\RentRequest;
use App\Models\Rent;

class RentController extends Controller
{
    public function index()
    {
        $rents = Rent::query()
            ->latest()
            ->get();

        return view('content.rents.index', compact('rents'));
    }

    public function create()
    {
        return view('content.rents.create');
    }

    public function store(RentRequest $request)
    {
        $rent = new Rent($request->payload());
        $rent->replaceStoredFile($request->file('con_pdf'), 'con_pdf', 'rents/pdf');
        $rent->replaceStoredFile($request->file('con_word'), 'con_word', 'rents/word');
        $rent->save();

        return redirect()
            ->route('rents.index')
            ->with('success', 'تم إضافة عقد الإيجار بنجاح.');
    }

    public function show(Rent $rent)
    {
        return redirect()->route('rents.edit', $rent);
    }

    public function edit(Rent $rent)
    {
        return view('content.rents.edit', compact('rent'));
    }

    public function update(RentRequest $request, Rent $rent)
    {
        $rent->fill($request->payload());
        $rent->replaceStoredFile($request->file('con_pdf'), 'con_pdf', 'rents/pdf');
        $rent->replaceStoredFile($request->file('con_word'), 'con_word', 'rents/word');
        $rent->save();

        return redirect()
            ->route('rents.index')
            ->with('success', 'تم تحديث عقد الإيجار بنجاح.');
    }

    public function destroy(Rent $rent)
    {
        $rent->deleteStoredFile('con_pdf');
        $rent->deleteStoredFile('con_word');
        $rent->delete();

        return redirect()
            ->route('rents.index')
            ->with('success', 'تم حذف عقد الإيجار بنجاح.');
    }
}
