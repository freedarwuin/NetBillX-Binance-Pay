{include file="sections/header.tpl"}

<form class="form-horizontal" method="post" role="form" action="{$_url}paymentgateway/binance">
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="panel panel-primary panel-hovered panel-stacked mb30">
                <div class="panel-heading">{Lang::T('Binance Pay Payment Gateway')}</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('API Key')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="binance_api_key" name="binance_api_key"
                                value="{$_c['binance_api_key']}">
                            <a href="https://merchant.binance.com/" target="_blank"
                                class="help-block">https://merchant.binance.com/</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Secret Key')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="binance_secret_key" name="binance_secret_key"
                                value="{$_c['binance_secret_key']}">
                            <a href="https://merchant.binance.com/" target="_blank"
                                class="help-block">https://merchant.binance.com/</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Currency')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="binance_currency">
                                {foreach $currency as $cur}
                                    <option value="{$cur}"
                                    {if $cur == $_c['binance_currency']}selected{/if}
                                    >{$cur}</option>
                                {/foreach}
                            </select>
                            <small class="form-text text-muted">
                                {Lang::T('Only supports stablecoins and major crypto assets.')}
                            </small>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button class="btn btn-primary waves-effect waves-light"
                                type="submit">{Lang::T('Save Change')}</button>
                        </div>
                    </div>
                    <pre>/ip hotspot walled-garden
add dst-host=binance.com
add dst-host=*.binance.com</pre>
                    <small class="form-text text-muted">
                        {Lang::T('Set Telegram Bot to get any error and notification')}
                    </small>
                </div>
            </div>

        </div>
    </div>
</form>

{include file="sections/footer.tpl"}