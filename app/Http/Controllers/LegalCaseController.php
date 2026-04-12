<?php

namespace App\Http\Controllers;

use App\Http\Requests\LegalCaseRequest;
use App\Models\CaseAttachment;
use App\Models\LegalCase;
use Illuminate\Http\UploadedFile;

class LegalCaseController extends Controller
{
    public function index()
    {
        $cases = LegalCase::query()
            ->withCount(['attachments', 'hearings'])
            ->latest()
            ->get();

        return view('content.cases.index', compact('cases'));
    }

    public function create()
    {
        return view('content.cases.create', [
            'primaryCourtOptions' => LegalCase::availablePrimaryCourts(),
            'appealCourtOptions' => LegalCase::availableAppealCourts(),
            'caseTypeOptions' => LegalCase::typeOptions(),
            'appealSessionPeriods' => LegalCase::appealSessionPeriodOptions(),
        ]);
    }

    public function store(LegalCaseRequest $request)
    {
        $case = LegalCase::create($request->payload());

        $this->storeAttachments($case, $request->file('attachments', []));

        return redirect()
            ->route('cases.index')
            ->with('success', 'تم إضافة القضية بنجاح.');
    }

    public function show(LegalCase $case)
    {
        return redirect()->route('cases.edit', $case);
    }

    public function edit(LegalCase $case)
    {
        $case->load('attachments');

        return view('content.cases.edit', [
            'case' => $case,
            'primaryCourtOptions' => LegalCase::availablePrimaryCourts(),
            'appealCourtOptions' => LegalCase::availableAppealCourts(),
            'caseTypeOptions' => LegalCase::typeOptions(),
            'appealSessionPeriods' => LegalCase::appealSessionPeriodOptions(),
        ]);
    }

    public function update(LegalCaseRequest $request, LegalCase $case)
    {
        $case->fill($request->payload());
        $case->save();

        $this->deleteSelectedAttachments($case, $request->input('remove_attachment_ids', []));
        $this->storeAttachments($case, $request->file('attachments', []));

        return redirect()
            ->route('cases.index')
            ->with('success', 'تم تحديث بيانات القضية بنجاح.');
    }

    public function destroy(LegalCase $case)
    {
        $case->load('attachments');

        $case->attachments->each(function (CaseAttachment $attachment): void {
            $attachment->deleteStoredFile('file_path');
            $attachment->delete();
        });

        $case->delete();

        return redirect()
            ->route('cases.index')
            ->with('success', 'تم حذف القضية بنجاح.');
    }

    protected function storeAttachments(LegalCase $case, array $files): void
    {
        collect($files)
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->each(function (UploadedFile $file) use ($case): void {
                $case->attachments()->create([
                    'file_path' => $file->store('case-attachments', 'public'),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            });
    }

    protected function deleteSelectedAttachments(LegalCase $case, array $attachmentIds): void
    {
        if ($attachmentIds === []) {
            return;
        }

        $case->attachments()
            ->whereIn('id', $attachmentIds)
            ->get()
            ->each(function (CaseAttachment $attachment): void {
                $attachment->deleteStoredFile('file_path');
                $attachment->delete();
            });
    }
}
