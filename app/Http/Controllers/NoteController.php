<?php

namespace App\Http\Controllers;

use App\Note;
use App\ProcessedContact;
use App\Services\RefKeyService;
use App\Traits\NoteTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    use NoteTrait;

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\ProcessedContact $processedContact
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(ProcessedContact $processedContact, Request $request, RefKeyService $refKeyService): JsonResponse
    {
        Note::create([
            'reference_key' => $refKeyService->getRefKey(new Note),
            'processed_contact_id' => $processedContact->id,
            'content' => $request->content
        ]);

        return response()->json([
            'status' => 'success',
            'notes' => $this->getGroupedNotesByDate($processedContact->notes)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\ProcessedContact $processedContact
     * @param \App\Note $note
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function update(ProcessedContact $processedContact, Note $note, Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Note $note
     * @return void
     */
    public function destroy(Note $note)
    {
        //
    }
}
