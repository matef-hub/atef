<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContractRequest;
use App\Models\Contract;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::query()
            ->latest()
            ->get();

        return view('content.contracts.index', compact('contracts'));
    }

    public function create()
    {
        return view('content.contracts.create', [
            'generatedContractNumber' => Contract::generateUniqueContractNumber(),
            'projectNames' => Contract::availableProjectNames(),
            'supplierNames' => Contract::availableSupplierNames(),
            'signOptions' => Contract::signOptions(),
        ]);
    }

    public function store(ContractRequest $request)
    {
        $contract = new Contract($request->payload());
        $contract->contr_number = Contract::resolveContractNumber($request->input('generated_contract_number'));
        $contract->replaceStoredFile($request->file('Pdf_image'), 'Pdf_image', 'contracts');
        $contract->save();

        return redirect()
            ->route('contracts.index')
            ->with('success', 'تم إضافة العقد بنجاح.');
    }

    public function show(Contract $contract)
    {
        return redirect()->route('contracts.edit', $contract);
    }

    public function edit(Contract $contract)
    {
        return view('content.contracts.edit', [
            'contract' => $contract,
            'projectNames' => Contract::availableProjectNames(),
            'supplierNames' => Contract::availableSupplierNames(),
            'signOptions' => Contract::signOptions(),
        ]);
    }

    public function update(ContractRequest $request, Contract $contract)
    {
        $contract->fill($request->payload());
        $contract->replaceStoredFile($request->file('Pdf_image'), 'Pdf_image', 'contracts');
        $contract->save();

        return redirect()
            ->route('contracts.index')
            ->with('success', 'تم تحديث العقد بنجاح.');
    }

    public function destroy(Contract $contract)
    {
        $contract->deleteStoredFile('Pdf_image');
        $contract->delete();

        return redirect()
            ->route('contracts.index')
            ->with('success', 'تم حذف العقد بنجاح.');
    }
}
