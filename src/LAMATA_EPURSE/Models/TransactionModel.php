<?php

namespace LAMATA_EPURSE\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
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
        $entryPoint = $details["entryPoint"];
        $entryTime = $details["entryTime"];
        $exitPoint = $details["exitPoint"];
        $exitTime = $details["exitTime"];
        $cardType = $details["cardType"];
        $cardSerial = $details["cardSerial"];
        $busID = $details["busID"];
        $amount = $details["amount"];

        $user = OrganizationModel::find($userID);
        if (!$user) {
            return ["data" => null, "error" => "Invalid transaction"];
        }

        if ((!isset($entryPoint) or !$entryPoint) and (!isset($exitPoint) or !$exitPoint)) {
            return ["data" => null, "error" => "entry point or exit point is required"];
        }

        if ((!isset($entryTime) or !$entryTime) and (isset($entryPoint) and $entryPoint)) {
            return ["data" => null, "error" => "entry time is required"];
        }

        if ((!isset($exitTime) or !$exitTime) and (isset($exitPoint) and $exitPoint)) {
            return ["data" => null, "error" => "exit time is required"];
        }

        if (
            $this->select("id")
                ->where("userID", $userID)
                ->where("entryPoint", $entryPoint)
                ->where("exitPoint", $exitPoint)
                ->where("cardSerial", $cardSerial)
                ->where("busID", $busID)
                ->where("entryTime", $entryTime)
                ->where("exitTime", $exitTime)
                ->exists()
        ) {
            return ["data" => null, "error" => "Duplicate transaction"];
        }

        $wallet = (new WalletModel)->getStruct()->where("userID", $userID)->latest()->first();

        $previousBalance = $wallet->currentBalance ?? 0;
        $currentBalance = $previousBalance + $amount;

        $this->userID = $userID;
        $this->entryPoint = $entryPoint;
        $this->entryTime = $entryTime;
        $this->exitPoint = $exitPoint;
        $this->exitTime = $exitTime;
        $this->cardType = $cardType;
        $this->cardSerial = $cardSerial;
        $this->busID = $busID;
        $this->amount = $amount;

        $this->save();
        (new WalletModel)->create(["userID" => $userID, "previousBalance" => $previousBalance, "currentBalance" => $currentBalance]);

        // $to = $user->email;
        // $name = $user->name;
        // $mail = new Mailer();
        // $mail->from = "info@touchandpay.me";
        // $mail->fromName = "Touchandpay";
        // $mail->to = $to;
        // $mail->toName = $name;
        // $mail->subject = "Lamata-Epurse Transaction Alert";
        // $mail->htmlBody = "<html><head><title>Lamata-Epurse transaction alert</title></head><body><h3>NAME: {$name}</h3><h3>AMOUNT: {$amount}</h3></h3></body></html>";
        // $mail->textBody = "Lamata-Epurse Transaction Alert \nNAME: {$name} \nAMOUNT: {$amount}";

        // ['error' => $error, 'success' => $success] = $mail->sendMail();


        return ['data' => ["success"], 'error' => ""];
    }

    public function getStruct()
    {
        return self::select('id', 'userID', 'entryPoint', 'entryTime', 'exitPoint', 'exitTime', 'cardType', 'cardSerial', 'busID', 'amount', 'dateCreated', 'dateUpdated');
    }

    private function getDiscount($member_type_id, $product_price)
    {
        $discount_ratio = 0;

        switch ($member_type_id) {
            case 1:
                $discount_ratio = 0.05;
                break;
            case 2:
                $discount_ratio = 0.10;
                break;
            case 3:
                $discount_ratio = 0.15;
                break;

            default:
                $discount_ratio = 0;
                break;
        }

        return  (int)$product_price - ($discount_ratio * (int)$product_price);
    }
}
