<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::query()
            ->latest()
            ->get();

        return view('content.documents.index', compact('documents'));
    }

    public function create()
    {
        return view('content.documents.create');
    }

    public function store(DocumentRequest $request)
    {
        $document = new Document($request->payload());
        $document->replaceStoredFile($request->file('docu_pdf'), 'docu_pdf', 'documents');
        $document->save();

        return redirect()
            ->route('documents.index')
            ->with('success', 'تم إضافة المستند بنجاح.');
    }

    public function show(Document $document)
    {
        return redirect()->route('documents.edit', $document);
    }

    public function edit(Document $document)
    {
        return view('content.documents.edit', compact('document'));
    }

    public function update(DocumentRequest $request, Document $document)
    {
        $document->fill($request->payload());
        $document->replaceStoredFile($request->file('docu_pdf'), 'docu_pdf', 'documents');
        $document->save();

        return redirect()
            ->route('documents.index')
            ->with('success', 'تم تحديث المستند بنجاح.');
    }

    public function destroy(Document $document)
    {
        $document->deleteStoredFile('docu_pdf');
        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'تم حذف المستند بنجاح.');
    }
}
