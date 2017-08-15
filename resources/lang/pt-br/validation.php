<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute precisa ser aceito.',
    'active_url'           => ':attribute infelizmente não é uma URL válida.',
    'after'                => ':attribute precisa ser uma data depois de :date. Por favor, você pode verificar isso?',
    'after_or_equal'       => ':attribute precisa ser uma data posterior ou igual a :date. Por favor, você pode verificar isso?',
    'alpha'                => ':attribute deve conter somente letras. Por favor, você pode verificar isso?',
    'alpha_dash'           => ':attribute deve conter letras, números e traços. Por favor, você pode verificar isso?',
    'alpha_num'            => ':attribute deve conter somente letras e números. Por favor, você pode verificar isso?',
    'array'                => ':attribute precisa ser um array. Por favor, você pode verificar isso?',
    'before'               => ':attribute precisa ser uma data antes de :date. Por favor, você pode verificar isso?',
    'before_or_equal'      => ':attribute precisa ser uma data anterior ou igual a :date. Por favor, você pode verificar isso?',
    'between'              => [
        'numeric' => 'Ops, :attribute deve estar entre :min e :max. Por favor, você pode verificar isso?',
        'file'    => 'Ops, :attribute deve estar entre :min e :max kilobytes. Por favor, você pode verificar isso?',
        'string'  => 'Ops, :attribute deve estar entre :min e :max caracteres. Por favor, você pode verificar isso?',
        'array'   => 'Ops, :attribute precisa ter entre :min e :max itens. Por favor, você pode verificar isso?',
    ],
    'boolean'              => ':attribute precisa ser verdadeiro ou falso. Por favor, você pode verificar isso?',
    'confirmed'            => 'A confirmação de :attribute não confere. Por favor, você pode verificar isso?',
    'date'                 => ':attribute infelizmente não é uma data válida. Por favor, você pode verificar isso?',
    'date_format'          => ':attribute infelizmente não confere com o formato :format. Por favor, você pode verificar isso?',
    'different'            => ':attribute e :other devem ser diferentes. Por favor, você pode verificar isso?',
    'digits'               => ':attribute precisa ter :digits dígitos. Por favor, você pode verificar isso?',
    'digits_between'       => ':attribute precisa ter entre :min e :max dígitos. Por favor, você pode verificar isso?',
    'dimensions'           => ':attribute tem dimensões de imagem inválidas. Por favor, você pode verificar isso?',
    'distinct'             => ':attribute tem um valor duplicado. Por favor, você pode verificar isso?',
    'email'                => ':attribute precisa ser um endereço de e-mail válido. Por favor, você pode verificar isso?',
    'exists'               => ':attribute selecionado é inválido. Por favor, você pode verificar isso?',
    'file'                 => ':attribute precisa ser um arquivo. Por favor, você pode verificar isso?',
    'filled'               => ':attribute é um campo requerido. Por favor, você pode verificar isso?',
    'image'                => ':attribute precisa ser uma imagem. Por favor, você pode verificar isso?',
    'in'                   => ':attribute é inválido. Por favor, você pode verificar isso?',
    'in_array'             => ':attribute não existe em :other. Por favor, você pode verificar isso?',
    'integer'              => ':attribute precisa ser um inteiro. Por favor, você pode verificar isso?',
    'ip'                   => ':attribute precisa ser um endereço IP válido. Por favor, você pode verificar isso?',
    'json'                 => ':attribute precisa ser um JSON válido. Por favor, você pode verificar isso?',
    'max'                  => [
        'numeric' => 'Ops, :attribute não precisa ser maior que :max. Por favor, você pode verificar isso?',
        'file'    => 'Ops, :attribute não precisa ter mais que :max kilobytes. Por favor, você pode verificar isso?',
        'string'  => 'Ops, :attribute não precisa ter mais que :max caracteres. Por favor, você pode verificar isso?',
        'array'   => 'Ops, :attribute não precisa ter mais que :max itens. Por favor, você pode verificar isso?',
    ],
    'mimes'                => ':attribute precisa ser um arquivo do tipo: :values. Por favor, você pode verificar isso?',
    'mimetypes'            => ':attribute precisa ser um arquivo do tipo: :values. Por favor, você pode verificar isso?',
    'min'                  => [
        'numeric' => ':attribute precisa ser no mínimo :min. Por favor, você pode verificar isso?',
        'file'    => ':attribute precisa ter no mínimo :min kilobytes. Por favor, você pode verificar isso?',
        'string'  => ':attribute precisa ter no mínimo :min caracteres. Por favor, você pode verificar isso?',
        'array'   => ':attribute precisa ter no mínimo :min itens. Por favor, você pode verificar isso?',
    ],
    'not_in'               => 'O :attribute selecionado é inválido. Por favor, você pode verificar isso?',
    'numeric'              => ':attribute precisa ser um número. Por favor, você pode verificar isso?',
    'present'              => 'O campo :attribute precisa ser presente. Por favor, você pode verificar isso?',
    'regex'                => 'O formato de :attribute é inválido. Por favor, você pode verificar isso?',
    'required'             => 'O campo :attribute precisa ser informado. Por favor, você pode verificar isso?',
    'required_if'          => 'O campo :attribute precisa ser informado quando :other é :value. Por favor, você pode verificar isso?',
    'required_unless'      => 'O :attribute é necessário a menos que :other esteja em :values. Por favor, você pode verificar isso?',
    'required_with'        => 'O campo :attribute precisa ser informado quando :values está presente. Por favor, você pode verificar isso?',
    'required_with_all'    => 'O campo :attribute precisa ser informado quando :values estão presentes. Por favor, você pode verificar isso?',
    'required_without'     => 'O campo :attribute precisa ser informado quando :values não está presente. Por favor, você pode verificar isso?',
    'required_without_all' => 'O campo :attribute precisa ser informado quando nenhum destes estão presentes: :values. Por favor, você pode verificar isso?',
    'same'                 => ':attribute e :other devem ser iguais.',
    'size'                 => [
        'numeric' => 'Ops, :attribute precisa ser :size. Por favor, você pode verificar isso?',
        'file'    => 'Ops, :attribute precisa ter :size kilobytes. Por favor, você pode verificar isso?',
        'string'  => 'Ops, :attribute precisa ter :size caracteres. Por favor, você pode verificar isso?',
        'array'   => 'Ops, :attribute deve conter :size itens. Por favor, você pode verificar isso?',
    ],
    'string'               => 'Ops, :attribute precisa ser uma string.',
    'timezone'             => 'Ops, :attribute precisa ser uma timezone válida.',
    'unique'               => 'Ops, :attribute já está em uso.',
    'uploaded'             => 'Ops, :attribute falhou ao ser enviado.',
    'url'                  => 'O formato de :attribute é inválido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
