<?php

return [
    'navigation' => [
        'page_label' => 'Registo de alterações',
        'resource_label' => 'Gerir registo de alterações',
    ],

    'model_label' => 'entrada do registo',
    'plural_model_label' => 'entradas do registo',

    'types' => [
        'added' => 'Adicionado',
        'changed' => 'Alterado',
        'deprecated' => 'Descontinuado',
        'removed' => 'Removido',
        'fixed' => 'Corrigido',
        'security' => 'Segurança',
    ],

    'fields' => [
        'project' => 'Projecto',
        'project_placeholder' => 'ex.: a-minha-app',
        'project_helper' => 'Opcional — deixe vazio se acompanhar apenas um projecto.',
        'version' => 'Versão',
        'version_placeholder' => '1.2.0 ou Unreleased',
        'released' => 'Lançado',
        'release_date' => 'Data de lançamento',
        'type' => 'Tipo',
        'description' => 'Descrição',
        'description_placeholder' => 'Descreva a alteração numa linha, ex.: "Adicionado modo escuro".',
    ],

    'actions' => [
        'import' => 'Importar',
        'export' => 'Exportar',
        'export_md' => 'Exportar .md',
        'manage' => 'Gerir',
    ],

    'import' => [
        'file_label' => 'Ficheiro CHANGELOG.md',
        'file_helper' => 'Carregue um ficheiro, ou cole o conteúdo abaixo.',
        'paste_label' => '…ou cole o markdown',
        'project_helper' => 'Marcar as entradas importadas com este projecto (opcional).',
        'replace_label' => 'Substituir primeiro as entradas existentes',
    ],

    'notifications' => [
        'nothing_to_import_title' => 'Nada para importar',
        'nothing_to_import_body' => 'Indique um ficheiro ou cole conteúdo markdown.',
        'no_entries_title' => 'Nenhuma entrada encontrada',
        'no_entries_body' => 'O conteúdo não corresponde ao formato Keep a Changelog.',
        'imported_title' => 'Importadas :count entradas',
    ],

    'reader' => [
        'unreleased' => 'Por lançar',
        'empty' => 'Ainda não há entradas no registo.',
    ],
];
