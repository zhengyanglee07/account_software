<?php

namespace App\Http\Controllers;

use App\Events\TagAddedToContact;
use App\Events\TagRemovedFromContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\ProcessedContact;
use App\ProcessedTag;
use App\Account;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TagsController extends Controller
{
    /**pass tag data */
    public function tags()
    {
        $user = Auth::user();
        $currentAccountId = $user->currentAccountId;
        $account = Account::where('id', $currentAccountId)->first();
        $accountRandomId = $account->accountRandomId;

        $processedTags = ProcessedTag::where('account_id', $currentAccountId)->where('typeOfTag', 'contact')->get();
        $dbTags = [];

        foreach ($processedTags as $processedTag) {
            $storeTag['people'] = count($processedTag->processedContacts); //total from modal processed_contact_processed_tag
            $storeTag['tagId'] = $processedTag->id;
            $storeTag['tagName'] = $processedTag->tagName;
            $dbTags[] = $storeTag;
        }

        return Inertia::render('people/pages/Tags', compact('dbTags', 'accountRandomId'));
    }

    public function refreshOption()
    {
        $user = Auth::user();
        $currentAccountId = $user->currentAccountId;
        $account = Account::where('id', $currentAccountId)->first();
        $accountRandomId = $account->accountRandomId;

        $processedTags = ProcessedTag::where('account_id', $currentAccountId)->where('typeOfTag', 'contact')->get();
        $tags = [];

        foreach ($processedTags as $processedTag) {
            $storeTag['id'] = $processedTag->id;
            $storeTag['tagId'] = $processedTag->id;
            $storeTag['tagName'] = $processedTag->tagName;
            $tags[] = $storeTag;
        }

        return response()->json([
            'tags' => $tags,
        ]);
    }

    public function createTag(Request $request)
    {
        $user = Auth::user();
        $currentAccountId = $user->currentAccountId;
        $account = Account::where('id', $currentAccountId)->first();

        $tag = ProcessedTag::where('account_id', $currentAccountId)->where('typeOfTag', 'contact')->where('tagName', $request->newTag)->first();
        $tags = [];

        //check duplication
        if ($tag) {
            $message = "This tag name already exist.";
        } else {
            $tag = new ProcessedTag();
            $tag->account_id = $currentAccountId;
            $tag->tagName = $request->newTag;
            $tag->typeOfTag = "contact";
            $tag->save();
            $message = "";
            $savedTags = ProcessedTag::where('account_id', $currentAccountId)->where('typeOfTag', 'contact')->get();
            foreach ($savedTags as $savedTag) {
                $storeTag['id'] = $savedTag->id;
                $storeTag['tagId'] = $savedTag->id;
                $storeTag['tagName'] = $savedTag->tagName;
                $storeTag['people'] = count($savedTag->processedContacts);
                $tags[] = $storeTag;
            }
        }

        return response()->json([
            'tags' => $tags,
            'message' => $message,
        ]);
    }

    public function renameTag(Request $request)
    {

        $user = Auth::user();
        $currentAccountId = $user->currentAccountId;

        $oldId = $request->oldTagId;
        $newName = $request->renameTag;

        $tag = ProcessedTag::where('account_id', $currentAccountId)->where('typeOfTag', 'contact')->where('tagName', $newName)->first();
        $newtags = [];

        if ($tag) {
            $message = "This tag name already exist.";
        } else {
            $processedTag = ProcessedTag::find($oldId);
            $processedTag->update(['tagName' => $newName]);

            $message = "";

            $savedTags = ProcessedTag::where('account_id', $currentAccountId)->where('typeOfTag', 'contact')->get();
            foreach ($savedTags as $savedTag) {
                $storeTag['tagId'] = $savedTag->id;
                $storeTag['tagName'] = $savedTag->tagName;
                $storeTag['people'] = count($savedTag->processedContacts);
                $newtags[] = $storeTag;
            }
        }

        return response()->json([
            'message' => $message,
            'newtags' => $newtags,
        ]);
    }

    public function deleteTag(Request $request)
    {
        $user = Auth::user();
        $currentAccountId = $user->currentAccountId;
        $account = Account::where('id', $currentAccountId)->first();

        $tags = [];
        $oldId = $request->oldTagId;
        $deleteTag = ProcessedTag::where('account_id', $currentAccountId)->where('id', $oldId)->first();
        $deleteTag->delete();

        $savedTags = ProcessedTag::where('account_id', $currentAccountId)->where('typeOfTag', 'contact')->get();
        foreach ($savedTags as $savedTag) {
            $storeTag['tagId'] = $savedTag->id;
            $storeTag['tagName'] = $savedTag->tagName;
            $storeTag['people'] = count($savedTag->processedContacts);
            $tags[] = $storeTag;
        }

        return response()->json([
            'tags' => $tags,
        ]);
    }

    /**Create an array(new tag) to store tag 1 and tag 2 data,
     * then delete tag1 and tag2*/
    public function mergeTag(Request $request)
    {
        $user = Auth::user();
        $currentAccountId = $user->currentAccountId;
        $account = Account::where('id', $currentAccountId)->first();

        $tagId = $request->getTagId;
        $selectedTag = $request->selectedTag;
        $newMergeName = $request->newMergeName;
        $storeContacts = [];

        $deleteTag1 = ProcessedTag::where('account_id', $currentAccountId)->where('id', $tagId)->first();
        foreach ($deleteTag1->processedContacts as $processedContact) {
            $storeContacts[] = $processedContact->id;
        }
        $deleteTag1->delete();

        $deleteTag2 = ProcessedTag::where('account_id', $currentAccountId)->where('id', $selectedTag)->first();
        foreach ($deleteTag2->processedContacts as $processedContact) {
            $storeContacts[] = $processedContact->id;
        }
        $deleteTag2->delete();

        $result = array_unique($storeContacts);

        $newTag = new ProcessedTag();
        $newTag->account_id = $currentAccountId;
        $newTag->tagName = $newMergeName;
        $newTag->typeOfTag = "contact";
        $newTag->save();

        $savedTag = ProcessedTag::where('account_id', $currentAccountId)->where('typeOfTag', 'contact')->where('tagName', $newMergeName)->first();

        foreach ($result as $contact) {
            $getContact = ProcessedContact::where('account_id', $currentAccountId)->where('id', $contact)->first();
            $getContact->processed_tags()->attach($savedTag->id);
        }

        return response()->json([
            'TagId' => $savedTag->id,
            'updatedTagName' => $savedTag->tagName,
            'people' => count($savedTag->processedContacts),
        ]);
    }

    /**Check Duplication */
    public function checkMergeName(Request $request)
    {
        $user = Auth::user();
        $currentAccountId = $user->currentAccountId;
        $account = Account::where('id', $currentAccountId)->first();

        $tagId = $request->getTagId;
        $selectedTag = $request->selectedTag;
        $newMergeName = $request->newMergeName;

        $deleteTag1 = ProcessedTag::where('account_id', $currentAccountId)->where('id', $tagId)->first();
        $tag1 = $deleteTag1->tagName;

        $deleteTag2 = ProcessedTag::where('account_id', $currentAccountId)->where('id', $selectedTag)->first();
        $tag2 = $deleteTag2->tagName;

        if ($tag1 == $newMergeName) {
            $message = "";
        } else if ($tag2 == $newMergeName) {
            $message = "";
        } else {
            $tag = ProcessedTag::where('account_id', $currentAccountId)->where('typeOfTag', 'contact')->where('tagName', $newMergeName)->first();
            if ($tag) {
                $message = "This tag name already exist.";
            } else {
                $message = "";
            }
        }

        return response()->json([
            'message' => $message,
        ]);
    }

    /**
     * Add tag to contacts, not create new tag.
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function addTag(Request $request): Response
    {
        $processedTag = ProcessedTag::findOrFail($request->input('selectedTag'));
        $contactIds = $request->input('contact');

        // Note: syncWithoutDetaching cannot handle extra column(s) in pivot table.
        //       Use back attach + diff if there's any new column(s)
        $processedTag->processedContacts()->syncWithoutDetaching($contactIds);

        event(new TagAddedToContact($processedTag, $contactIds));

        return response()->noContent();
    }

    /**
     * Remove tag from contacts, not delete ProcessedTag itself
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function removeTag(Request $request): Response
    {
        $processedTag = ProcessedTag::findOrFail($request->input('selectedTag'));
        $contactIds = $request->input('contact');
        $processedTag->processedContacts()->detach($contactIds);

        event(new TagRemovedFromContact($processedTag, $contactIds));

        return response()->noContent();
    }

    /**
     * Select tags from people profile home and add them into database simultaneously
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectTag(Request $request): JsonResponse
    {
        $user  = Auth::user();
        $currentAccountId = $user->currentAccountId;
        $processedTagArray = [];
        $newProcessedTag = ProcessedTag::firstOrCreate(
            [
                'tagName' => $request->newTag,
                'account_id' => $currentAccountId,
            ],
            [
                'typeOfTag' => 'contact',
            ]
            );
            
        $newProcessedTag->processedContacts()->syncWithoutDetaching($request->input('currentContact'));
        $newProcessedContactProcessedTag = DB::table('processed_contact_processed_tag')
            ->where('processed_contact_id', $request->input('currentContact'))
            ->pluck('processed_tag_id');

        foreach ($newProcessedContactProcessedTag as $id)
        {
            $newProcessedTag = ProcessedTag::find($id);
            array_push($processedTagArray, $newProcessedTag);
        }

        event(new TagAddedToContact($newProcessedTag, [$request->input('currentContact')]));
        
        return response()->json(['processedTagArray' => $processedTagArray]);
    }

    public function deleteSelectedTag(Request $request): JsonResponse
    {

        $deleteTag = ProcessedTag::findOrFail($request->input('selectTag'));
        $processedContactId = $request->input('currentContact');
        $deleteTag->processedContacts()->detach($processedContactId);
        $getNewArray = DB::table('processed_contact_processed_tag')->get();

        event(new TagRemovedFromContact($deleteTag, [$processedContactId]));

        return response()->json(['processedTag' => $getNewArray]);
    }
}
