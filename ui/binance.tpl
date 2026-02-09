{include file="sections/header.tpl"}

<form class="form-horizontal" method="post" action="{$_url}paymentgateway/binance">
    <div class="row">
        <div class="col-sm-12 col-md-12">
            <div class="panel panel-primary panel-hovered panel-stacked mb30">
                <div class="panel-heading">{Lang::T('Binance Pay Payment Gateway')}</div>

                <div class="panel-body">

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('API Key')}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control"
                                   name="binance_api_key"
                                   value="{$_c['binance_api_key']}" required>
                            <a href="https://merchant.binance.com/" target="_blank"
                               class="help-block">https://merchant.binance.com/</a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Secret Key')}</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control"
                                   name="binance_secret_key"
                                   value="{$_c['binance_secret_key']}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{Lang::T('Payment Currency')}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="binance_currency" required>
                                {foreach $currency as $cur}
                                    <option value="{$cur}"
                                    {if $cur == $_c['binance_currency']}selected{/if}>
                                        {$cur}
                                    </option>
                                {/foreach}
                            </select>
                            <small class="form-text text-muted">
                                {Lang::T('Only cryptocurrencies supported by Binance Pay are allowed.')}
                            </small>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button class="btn btn-primary" type="submit">
                                {Lang::T('Save Changes')}
                            </button>
                        </div>
                    </div>

                    <hr>

                    <pre>/ip hotspot walled-garden
add dst-host=binance.com
add dst-host=*.binance.com</pre>

                    <small class="form-text text-muted">
                        {Lang::T('Required for Mikrotik Hotspot users to access Binance Pay checkout.')}
                    </small>

                </div>
            </div>
        </div>
    </div>
</form>

{include file="sections/footer.tpl"}
