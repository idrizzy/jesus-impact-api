<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\User;
use Auth;
use App\Events\NewMessage;
class MessageController extends Controller
{
    public function get()
    {
        $contacts = User::whereHas('roles', function($q){
            $q->where('name','!=', 'SuperAdmin');
        })->where('id','!=',Auth::id())->get();
        $unreadIds = Message::select(
                                        \DB::raw('`from` as sender_id, count(`from`) as messages_count, `text` as convo'))
            ->where('to', auth()->id())
            ->where('read', false)
            ->groupBy('from','text')
            ->get();
        $unreadIdss = Message::select(
                                        \DB::raw('`from` as sender_id, count(`from`) as messages_count, `text` as convo'))
            ->where('to', auth()->id())
            ->groupBy('from','text')
            ->get();

        // add an unread key to each contact with the count of unread messages
        $contacts = $contacts->map(function($contact) use ($unreadIds,$unreadIdss) {
            $contactUnread = $unreadIds->where('sender_id', $contact->id)->first();
            $contactUnreads = $unreadIdss->where('sender_id', $contact->id)->first();

            $contact->unread = $contactUnread ? $contactUnread->messages_count : 0;
            $contact->message = $contactUnreads ? $contactUnreads->convo : '';
            return $contact;
        });


        return response()->json(['data'=>  $contacts],200);
    }

    public function getMessagesFor($id)
    {
        // mark all messages with the selected contact as read
        Message::where('from', $id)->where('to', auth()->id())->update(['read' => true]);

        // get all messages between the authenticated user and the selected user
        $messages = Message::where(function($q) use ($id) {
            $q->where('from', auth()->id());
            $q->where('to', $id);
        })->orWhere(function($q) use ($id) {
            $q->where('from', $id);
            $q->where('to', auth()->id());
        })
        ->get();

        return response()->json(['data'=>  $messages],200);
    }
    public function send(Request $request)
    {
        $message = Message::create([
            'from' => auth()->id(),
            'to' => $request->contact_id,
            'text' => $request->text
        ]);

        return response()->json(['data'=>  $message],200);
    }
}
