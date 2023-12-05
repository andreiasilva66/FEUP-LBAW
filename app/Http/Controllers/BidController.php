<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bid;
use App\Models\User;
use App\Models\Auction;
use App\Http\Controllers\AuctionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use League\CommonMark\Node\Query;

class BidController extends Controller
{
    public function create(Request $request, $auction_id)
    {
      //dd($request->all());
      $user = Auth::user();
      //Log::info("User {$user->id} type: {$user->type} username: {$user->username}");
      $bid = new Bid();
      $bid->user_id = $user->id;
      $bid->auction_id = $auction_id;
      $bid->amount = $request->input('amount');

      //find top bid
      $auction = Auction::find($auction_id);
        $bids = $auction->bids()->orderBy('amount', 'desc')->get();
        if (count($bids) != 0) {
          $topBid = $bids[0];
        }
        else {
          $topBid = null;
        }
      try {
        $this->authorize('bid', $topBid);
        $bid->save();
    } catch (AuthorizationException $e) {
        return back()->with('error', 'You are not authorized to perform this action.');
    }
      catch (QueryException $e) {
        return back()->with('error', 'You are not authorized to perform this action.');
      }
      return view('pages.auction',['auction' => $auction]);
    }

    public function showCreateForm($auction_id)
    {
      $auction = Auction::find($auction_id);
        $bids = $auction->bids()->orderBy('amount', 'desc')->get();
        if (count($bids) != 0) {
          $topBid = $bids[0];
        }
        else {
          $topBid = null;
        }
      $this->authorize('create', $topBid);
      return view('pages.createBid', ['id' => $auction_id]);
    }

    public function biddedBy($user_id)
    {
      $bids = Bid::get()->where('user_id', $user_id);
      return view('pages.ownedBids', ['bids' => $bids]);
    }

    public function show($id)
    {
      $bid = Bid::find($id);
      return view('pages.ownedBids', ['bid' => $bid, 'user' => $bid->user_id]);
    }

}
