<?php

class WhoopsHook
{
    public function bootWhoops()
    {
        $whoops = new \Whoops\Run;
        $handler = new \Whoops\Handler\PrettyPageHandler();
        if (ENVIRONMENT === 'production') {
            $sensitive_vars = [
                'APP_ENCRYPTION_KEY',
                'DB_USERNAME',
                'DB_PASSWORD',
                'DB_HOSTNAME',
                'DB_DATABASE',
                'EMAIL_PROTOCOL',
                'EMAIL_SMTP_HOST',
                'EMAIL_SMTP_USER',
                'EMAIL_SMTP_PASS',
                'PAYMENT_GATEWAYS_EFI_PRODUCTION',
                'PAYMENT_GATEWAYS_EFI_CREDENTIAIS_CLIENT_ID',
                'PAYMENT_GATEWAYS_EFI_CREDENTIAIS_CLIENT_SECRET',
                'PAYMENT_GATEWAYS_EFI_TIMEOUT',
                'PAYMENT_GATEWAYS_EFI_BOLETO_EXPIRATION',
                'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_ACCESS_TOKEN',
                'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_PUBLIC_KEY',
                'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_CLIENT_ID',
                'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_CLIENT_SECRET',
                'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_INTEGRATOR_ID',
                'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_PLATFORM_ID',
                'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_CORPORATION_ID',
                'PAYMENT_GATEWAYS_MERCADO_PAGO_BOLETO_EXPIRATION',
                'PAYMENT_GATEWAYS_ASAAS_PRODUCTION',
                'PAYMENT_GATEWAYS_ASAAS_NOTIFY',
                'PAYMENT_GATEWAYS_ASAAS_CREDENTIAIS_API_KEY',
                'PAYMENT_GATEWAYS_ASAAS_BOLETO_EXPIRATION',
                'API_ENABLED',
                'API_JWT_KEY',
                'API_TOKEN_EXPIRE_TIME',
            ];

            foreach ($sensitive_vars as $var) {
                $handler->blacklist('_SERVER', $var);
                $handler->blacklist('_ENV', $var);
            }
        }

        $whoops->pushHandler($handler);

        $whoops->register();
    }
}
