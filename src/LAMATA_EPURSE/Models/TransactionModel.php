<?php

namespace LAMATA_EPURSE\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use LAMATA_EPURSE\Domain\Constants;
use Rashtell\Domain\CodeLibrary;
use Rashtell\Domain\Mailer;

class TransactionModel extends BaseModel
{
    use SoftDeletes;

    protected $table = 'transactions';

    const CREATED_AT = 'dateCreated';
    const UPDATED_AT = 'dateUpdated';
    const DELETED_AT = 'dateDeleted';

    protected $dateFormat = 'U';

    public function user()
    {
        return $this->belongsTo(UserModel::class, "userID");
    }

    public function create($details)
    {
        $userID = $details["userID"];

        $cardType = $details["cardType"];
        if (!in_array($cardType, Constants::CARD_TYPE)) {
            return ["data" => null, "error" => ["type" => "error", "message" => "Invalid card type"]];
        }

        $transID = $details["transID"];
        if (
            $this->select("id")
            ->where("transID", $transID)
            ->exists()
        ) {
            return ["data" => null, "error" => ["type" => "error", "message" => "Duplicate transaction, transaction ID exists"]];
        }

        $amount = (int) $details["amount"];

        $transType = $details["transType"];
        if ($transType == Constants::TRANSACTION_CHECK_IN) {
            return $this->createCheckInTransaction($details);
        } elseif ($transType == Constants::TRANSACTION_CHECK_OUT) {
            return $this->createCheckOutTransaction($details);
        } else {
            return ['data' => null, 'error' => ["type" => "error", 'Invalid transaction type. Transaction type should either be CHECK_IN or CHECK_OUT']];
        }

        $user = OrganizationModel::find($userID);
        if (!$user) {
            return ["data" => null, "error" => ["type" => "error", "Invalid transaction"]];
        }

        $to = $user->email;
        $name = $user->name;
        $mail = new Mailer();
        $mail->from = "info@touchandpay.me";
        $mail->fromName = "Touchandpay";
        $mail->to = $to;
        $mail->toName = $name;
        $mail->subject = "Lamata-Epurse Transaction Alert";
        $mail->htmlBody = "<html><head><title>Lamata-Epurse transaction alert</title></head><body><h3>NAME: {$name}</h3><h3>AMOUNT: {$amount}</h3></h3></body></html>";
        $mail->textBody = "Lamata-Epurse Transaction Alert \nNAME: {$name} \nAMOUNT: {$amount}";

        ['error' => $error, 'success' => $success] = $mail->sendMail();


        return ['data' => $success, 'error' => $error];
    }

    private function createCheckInTransaction($details)
    {
        $transType = Constants::TRANSACTION_CHECK_IN;
        $userID = $details["userID"];
        $cardType = $details["cardType"];
        $cardSerial = $details["cardSerial"];
        $busID = $details["busID"];
        $amount = $details["amount"];
        $tripID = $details["tripID"];
        $transID = $details["transID"];

        $entryPoint = $details["entryPoint"];
        if ((!isset($entryPoint) or !$entryPoint)) {
            return ["data" => null, "error" => ["type" => "error", "message" => "Entry point is required"]];
        }

        $entryTime = $details["entryTime"];
        if ((!isset($entryTime) or !$entryTime)) {
            return ["data" => null, "error" => ["type" => "error", "message" => "Entry time is required"]];
        }

        $user = OrganizationModel::find($userID);
        if (!$user) {
            return ["data" => null, "error" => ["type" => "error", "message" => "Invalid transaction"]];
        }

        $checkID = null;
        do {
            $checkID = (new CodeLibrary())->genID(100);
        } while ($this->where("checkID", $checkID)->exists());

        if (
            $this->select("id")
            ->where("transType", $transType)
            ->where("userID", $userID)
            ->where("entryPoint", $entryPoint)
            ->where("entryTime", $entryTime)
            ->where("cardSerial", $cardSerial)
            ->where("busID", $busID)
            ->where("tripID", $tripID)
            ->exists()
        ) {
            return ["data" => null, "error" => ["type" => "error", "message" => "Duplicate transaction"]];
        }

        $amount = (int)$amount;

        $wallet = (new WalletModel)->getStruct()->where("userID", $userID)->latest()->first();

        $previousBalance = $wallet->currentBalance ?? 0;
        $currentBalance = $previousBalance + $amount;

        $this->userID = $userID;
        $this->checkID = $checkID;
        $this->transType = $transType;
        $this->transID = $transID;
        $this->tripID = $tripID;
        $this->entryPoint = $entryPoint;
        $this->entryTime = $entryTime;
        $this->cardType = $cardType;
        $this->cardSerial = $cardSerial;
        $this->busID = $busID;
        $this->amount = $amount;
        $this->maxFee = $amount;

        $this->save();
        (new WalletModel)->create(["userID" => $userID, "previousBalance" => $previousBalance, "currentBalance" => $currentBalance, "transactionType" => "CREDIT"]);

        return ['data' => ["type" => "success", "transID" => $transID, "message" => "Check-in success"], 'error' => ""];
    }

    private function createCheckOutTransaction($details)
    {
        $transType = Constants::TRANSACTION_CHECK_OUT;
        $userID = $details["userID"];
        $cardType = $details["cardType"];
        $cardSerial = $details["cardSerial"];
        $busID = $details["busID"];
        $amount = $details["amount"];
        $tripID = $details["tripID"];
        $transID = $details["transID"];

        $exitPoint = $details["exitPoint"];
        if ((!isset($exitPoint) or !$exitPoint)) {
            return ["data" => null, "error" => ["type" => "error", "message" => "Exit point is required"]];
        }

        $exitTime = $details["exitTime"];
        if ((!isset($exitTime) or !$exitTime)) {
            return ["data" => null, "error" => ["type" => "error", "message" => "Exit time is required"]];
        }

        $user = OrganizationModel::find($userID);
        if (!$user) {
            return ["data" => null, "error" => ["type" => "error", "message" => "Invalid transaction"]];
        }

        if (!$this->where("tripID", $tripID)->where("cardSerial", $cardSerial)->where("busID", $busID)->exists()) {
            return ["data" => null, "error" => ["type" => "error", "message" => "No corresponding check-in transaction found"]];
        }
        $checkInTransaction = $this->where("tripID", $tripID)->where("cardSerial", $cardSerial)->where("busID", $busID)->first();

        if ($checkInTransaction["transType"] == Constants::TRANSACTION_CHECK_IN_OUT) {
            return ["data" => null, "error" => ["type" => "error", "message" => "Transaction previously checked out"]];
        }

        $checkID = $checkInTransaction["checkID"];

        if (
            $this->select("id")
            ->where("transType", $transType)
            ->where("userID", $userID)
            ->where("exitPoint", $exitPoint)
            ->where("exitTime", $exitTime)
            ->where("cardSerial", $cardSerial)
            ->where("busID", $busID)
            ->where("tripID", $tripID)
            ->exists()
        ) {
            return ["data" => null, "error" => ["type" => "error", "message" => "Duplicate transaction"]];
        }

        $amount = (int)$amount;

        $wallet = (new WalletModel)->getStruct()->where("userID", $userID)->latest()->first();

        $previousBalance = $wallet->currentBalance ?? 0;
        $currentBalance = $previousBalance - $amount;

        // $this->userID = $userID;
        // $this->transType = $transType;
        // $this->checkID = $checkID;
        // $this->transID = $transID;
        // $this->tripID = $tripID;
        // $this->exitPoint = $exitPoint;
        // $this->exitTime = $exitTime;
        // $this->cardType = $cardType;
        // $this->cardSerial = $cardSerial;
        // $this->busID = $busID;
        // $this->amount = $amount;

        // $this->save();

        $checkInTransaction->transType = Constants::TRANSACTION_CHECK_IN_OUT;
        $checkInTransaction->exitPoint = $exitPoint;
        $checkInTransaction->exitTime = $exitTime;
        $checkInTransaction->amount = (int)$checkInTransaction["amount"] - $amount;
        $checkInTransaction->changeFee = $amount;
        $checkInTransaction->save();
        (new WalletModel)->create(["userID" => $userID, "previousBalance" => $previousBalance, "currentBalance" => $currentBalance, "transactionType" => "DEBIT"]);

        return ['data' => ["type" => "success", "transID" => $transID, "message" => "Check-out success"], 'error' => ""];
    }

    public function getStruct()
    {
        return self::select('id', 'transID', 'tripID', 'entryPoint', 'entryTime', 'exitPoint', 'exitTime', 'cardType', 'cardSerial', 'busID', 'amount', 'maxFee', 'changeFee', 'dateCreated', 'dateUpdated');
    }
}
