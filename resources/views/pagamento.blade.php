<?php
$req = Request();
use Illuminate\Support\Facades\Crypt;

if (!$req->has('total') || !$req->has('nome-do-produto')) {
redirect('/');
}

$produtoInfo = [
'valor' => sprintf('%.2f', floatval(preg_replace('/[^\d]/', '', $req->input('total')))),
'nome' => $req->input('nome-do-pedido'),
];

if ($produtoInfo['valor'] <= 0.0) { redirect('/'); } ?> <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="{{ asset('styles/pagamento.css') }}">
        <script defer src="https://use.fontawesome.com/releases/v5.14.0/js/all.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css">
        <title>eRede gateway</title>
    </head>

    <body>
        <div class="notification is-info">
            Essa aplicação está no modo <strong>SANDBOX</strong>, utilize algum <a
                href="https://www.userede.com.br/desenvolvedores/pt/produto/e-Rede#tutorial-cartao"
                target="_blank">DESTES</a>
            cartões de créditos para testar a integração, caso queira teste os erros de pagamento, <a
                href="https://www.userede.com.br/desenvolvedores/pt/produto/e-Rede#tutorial-erros" target="_blank">AQUI</a> está a lista
            de
            <strong>VALORES</strong> e seus respectivos <strong>ERROS</strong>
        </div>
        <form class="payment">
            <input type="hidden" name="productData" value="<?= Crypt::encryptString(json_encode($produtoInfo)) ?>" />

            <div class="box is-main">
                <div class="box-head">
                    <p class="subtitle is-4 is-box-head-title"><strong>PAGAMENTO</strong></p>
                </div>
                <div class="box">
                    <div id="box-body" class="box-body">
                        <div class='card-wrapper'></div>
                        <span class="subtitle is-6 is-cartao">Numero do cartão</span>
                        <div class="control has-icons-left">
                            <input id="input" class="input is-small" maxlength="19" type="text" name="number"
                                placeholder="**** **** **** ****" autocomplete="off" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-credit-card"></i>
                            </span>
                        </div>

                        <span class="subtitle is-6">Primeiro nome</span>
                        <div class="control has-icons-left">
                            <input class="input is-small" type="text" name="first-name" autocomplete="off" required />
                            <span class="icon is-small is-left">
                                <i class="far fa-address-card"></i>
                            </span>
                        </div>
                        <span class="subtitle is-6">Ultimo nome</span>
                        <div class="control has-icons-left">
                            <input class="input is-small" type="text" name="last-name" autocomplete="off" required />
                            <span class="icon is-small is-left">
                                <i class="far fa-address-card"></i>
                            </span>
                        </div>
                        <span class="subtitle is-6">Validade</span>
                        <div class="control has-icons-left">
                            <input class="input is-small" type="text" name="expiry" maxlength="9" autocomplete="off"
                                required />
                            <span class="icon is-small is-left">
                                <i class="far fa-address-card"></i>
                            </span>
                        </div>
                        <span class="subtitle is-6">CVV</span>
                        <div class="control has-icons-left">
                            <input class="input is-small" type="password" name="cvc" autocomplete="off" required />
                            <span class="icon is-small is-left">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                        <span class="subtitle is-6">Parcelas</span>
                        <div class="control has-icons-left">
                            <div class="select is-fullwidth" id="parcelas">
                                <select name="parcelas">
                                    <option value="1">1 vez</option>
                                    <option value="2">2 vezes</option>
                                    <option value="3">3 vezes</option>
                                    <option value="4">4 vez</option>
                                    <option value="5">5 vezes</option>
                                    <option value="6">6 vezes</option>
                                    <option value="7">7 vez</option>
                                    <option value="8">8 vezes</option>
                                    <option value="9">9 vezes</option>
                                    <option value="10">10 vez</option>
                                    <option value="11">11 vezes</option>
                                    <option value="12">12 vezes</option>
                                </select>
                            </div>
                            <div class="icon is-small is-left">
                                <i class="fas fa-receipt"></i>
                            </div>

                        </div>
                        <button type="submit" class="button is-pay is-fullwidth">PAGAR {{ $req->total ?? 'R$00,00' }}</button>
                    </div>
                </div>
            </div>
        </form>
        <div id="modal" class="modal">
            <div class="modal-background"></div>
            <div class="modal-card">
              <header class="modal-card-head">
                <p class="modal-card-title" id="modal-title"><strong></strong></p>
              </header>
              <section class="modal-card-body">
                <span class="subtitle is-5 is-modal" id="modal-message"></span>
              </section>
              <footer class="modal-card-foot">
                <button id="delete" class="button is-ok"><strong>Fechar</strong></button>
              </footer>
            </div>
          </div>
    <script src="{{ asset('styles/card.js') }}"></script>
    <script>
        var card = new Card({
            form: document.getElementById('box-body'),
            container: '.card-wrapper',
            placeholders: {
                number: '**** **** **** ****',
                name: 'Nome completo',
                expiry: '**/****',
                cvc: '***'
            },
            width:250,
            masks: {
                cardNumber: '•'
            },
            formSelectors: {
                nameInput: 'input[name="first-name"], input[name="last-name"]'
            }
        });

        document.querySelector('#delete').addEventListener('click',(event)=>{
            document.getElementById('modal').classList.remove('is-active');
        })
        var formatter = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',

        // These options are needed to round to whole numbers if that's what you want.
        //minimumFractionDigits: 0,
        //maximumFractionDigits: 0,
        });
        document.querySelector('.payment')
        .addEventListener('submit', (event) => {
            event.preventDefault();
            let fd = new FormData(document.querySelector('.payment'));

            fetch('{{ route('api-payment') }}', {
                body: fd,
                method: 'POST'
            })
            .then(response => response.text())
            .then(body => {
                console.log(body);
                try{
                    var obj = JSON.parse(body);
                    document.getElementById('modal').classList.add('is-active');
                    document.getElementById('modal-title').innerHTML= obj.success ? 'Sucesso' : 'Erro';
                    document.getElementById('modal-message').innerHTML = obj.success ? 
                    `Nome do comprador: ${obj.message.cardHolderName} <br>
                    Parcelas: ${obj.message.installments} <br>
                    Valor: ${formatter.format(obj.message.amount / 100)}` 
                    : "Retorno: " +  obj.message;
                }catch(e)
                {
                    alert(e);
                }
            });
        });
    </script>
</body>

</html>
