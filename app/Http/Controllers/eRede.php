<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Illuminate\Support\Facades\Crypt;
use Rede\Store;
use Rede\Environment;
use Rede\Transaction;
use Rede\eRede as RedeFinal;

class eRede extends Controller
{
    /**
     * Vars
     */
    private $loja;
    private $valor;
    private $produto;
    private $logger;
    private $transaction;
    private $infos;

    private function genRef($pedido, $pedidoId = 0)
    {
        return crc32(sprintf('%s-%s', $pedido, $pedidoId === 0 ? time() : $pedidoId));
    }

    private function Cartao(Request $r)
    {
        $expiry = $r["expiry"];
        $expiry = str_replace(" ", "", $expiry);
        $expiry = explode('/', $expiry);
        $number = str_replace(" ", "", $r["number"]);
        $name = $r["first-name"] . " " .  $r["last-name"];
        $this->infos = [
            "number" => $number ?? "",
            "name" => $name ?? "",
            "month" => $expiry[0] ?? "00",
            "year" => $expiry[1] ?? "0000",
            "cvv" => $r["cvc"] ?? "000",
            "parcelas" => $r["parcelas"] ?? 1
        ];
    }

    private function Loja(Request $r)
    {
        $productInfo = json_decode(Crypt::decryptString($r->input('productData')), true);

        $this->produto = $productInfo['nome'] ?? "N/A";
        $this->valor   = $productInfo['valor'] ?? "00";
        if (empty(getenv('EREDE_PV')) or empty(getenv('EREDE_TOKEN')))
            return json_encode(['status' => 500, 'message' => 'TOKEN do eRede está vazio, contate o administrador do site.']);

        $this->logger = new Logger('eRede SDK Test');
        $this->logger->pushHandler(new StreamHandler('php://stdout'), Logger::DEBUG);

        $this->loja = new Store(getenv('EREDE_PV'), getenv('EREDE_TOKEN'), Environment::sandbox());
    }

    private function Transcao(Request $r)
    {
        $trans = new Transaction($this->valor, $this->genRef($this->produto));
        $trans = $trans->creditCard(
            $this->infos["number"],
            $this->infos["cvv"],
            $this->infos["month"],
            $this->infos["year"],
            $this->infos["name"]
        )->capture(true)->setInstallments($this->infos["parcelas"]);
        $this->transaction = new RedeFinal($this->loja, $this->logger);

        $this->transaction = $this->transaction->create($trans);
    }

    public function Pagar(Request $r)
    {
        try {
            $this->Cartao($r);
            $this->Loja($r);
            $this->Transcao($r);
            $txData = $this->transaction->jsonSerialize();

            return response()->json([
                'success' => true,
                'message' => [
                    "kind" => $txData["kind"], 
                    "amount" => $txData["amount"] / 100,
                    "reference" => $txData["reference"],
                    "installments" => $txData["installments"],
                    "cardHolderName" => $txData["cardHolderName"],
                    "cardNumber" => str_pad(substr($txData["cardNumber"], -4), 16, "*", STR_PAD_LEFT),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
