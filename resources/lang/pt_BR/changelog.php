<?php

return [
    'navigation' => [
        'page_label' => 'Registro de alterações',
        'resource_label' => 'Gerenciar registro de alterações',
    ],

    'model_label' => 'entrada do registro',
    'plural_model_label' => 'entradas do registro',

    'types' => [
        'added' => 'Adicionado',
        'changed' => 'Alterado',
        'deprecated' => 'Descontinuado',
        'removed' => 'Removido',
        'fixed' => 'Corrigido',
        'security' => 'Segurança',
    ],

    'fields' => [
        'project' => 'Projeto',
        'project_placeholder' => 'ex.: meu-app',
        'project_helper' => 'Opcional — deixe vazio ao acompanhar apenas um projeto.',
        'version' => 'Versão',
        'version_placeholder' => '1.2.0 ou Unreleased',
        'released' => 'Lançado',
        'release_date' => 'Data de lançamento',
        'type' => 'Tipo',
        'description' => 'Descrição',
        'description_placeholder' => 'Descreva a alteração em uma linha, ex.: "Adicionado modo escuro".',
    ],

    'actions' => [
        'import' => 'Importar',
        'export' => 'Exportar',
        'export_md' => 'Exportar .md',
        'manage' => 'Gerenciar',
    ],

    'import' => [
        'file_label' => 'Arquivo CHANGELOG.md',
        'file_helper' => 'Envie um arquivo, ou cole o conteúdo abaixo.',
        'paste_label' => '…ou cole o markdown',
        'project_helper' => 'Marcar as entradas importadas com este projeto (opcional).',
        'replace_label' => 'Substituir primeiro as entradas existentes',
    ],

    'notifications' => [
        'nothing_to_import_title' => 'Nada para importar',
        'nothing_to_import_body' => 'Informe um arquivo ou cole conteúdo markdown.',
        'no_entries_title' => 'Nenhuma entrada encontrada',
        'no_entries_body' => 'O conteúdo não corresponde ao formato Keep a Changelog.',
        'imported_title' => 'Importadas :count entradas',
    ],

    'reader' => [
        'unreleased' => 'Não lançado',
        'empty' => 'Ainda não há registros no changelog.',
        'search_placeholder' => 'Pesquisar no changelog…',
        'all_versions' => 'Todas as versões',
        'no_results' => 'Nenhum registro corresponde à pesquisa.',
        'loading' => 'Carregando mais…',
    ],
];
