<?php

/***************************************************************************
 *
 *    ougc Awards plugin (/inc/anguages/english/admin/user_ougc_awards.lang.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2012 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Manage a powerful awards system for your community.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

$l = [
    'ougcAwards' => 'Premios ougc',
    'ougcAwardsDescription' => 'Gestiona un poderoso sistema de premios para tu comunidad.',

    'setting_group_ougc_awards' => 'Premios',
    'setting_group_ougc_awards_desc' => 'Gestiona un poderoso sistema de premios para tu comunidad.',

    'ougc_awards_import_title' => 'Importar Premios',
    'ougcAwardsImportDescription' => '<br />&nbsp;&nbsp;&nbsp;<a href="./index.php?module=config-plugins&amp;ougc_awards_import=myawards">Importar Desde MyAwards</a>
<br />&nbsp;&nbsp;&nbsp;<a href="./index.php?module=config-plugins&amp;ougc_awards_import=nickawards">Importar Desde el Sistema de Premios de Nickman</a>',
    'ougc_awards_import_confirm_myawards' => '¿Estás seguro de que deseas importar premios de MyAwards?',
    'ougc_awards_import_confirm_nickawards' => '¿Estás seguro de que deseas importar premios del sistema de premios de Nickman?',
    'ougc_awards_import_end' => 'Premios importados con éxito.',
    'ougc_awards_import_error' => 'Hubo un error al intentar importar los premios seleccionados. La tabla "{1}" no parece existir.',

    'setting_ougc_awards_showInPosts' => 'Máximo de Premios en Mensajes',
    'setting_ougc_awards_showInPosts_desc' => 'Número máximo de premios que se mostrarán en los mensajes.',
    'setting_ougc_awards_groupAwardGrantsInPosts' => 'Agrupar Premios en Mensajes',
    'setting_ougc_awards_groupAwardGrantsInPosts_desc' => 'Si activas esto, los premios se mostraran solo una vez por usuario en los mensajes',
    'setting_ougc_awards_showInPostsPresets' => 'Máximo de Presets en Mensajes',
    'setting_ougc_awards_showInPostsPresets_desc' => 'Escribe el número máximo de premios preestablecidos para mostrar en los mensajes.',
    'setting_ougc_awards_showInProfile' => 'Máximo de Premios en el Perfil',
    'setting_ougc_awards_showInProfile_desc' => 'Número máximo de premios que se mostrarán en los perfiles.',
    'setting_ougc_awards_groupAwardGrantsInProfiles' => 'Agrupar Premios en Perfiles',
    'setting_ougc_awards_groupAwardGrantsInProfiles_desc' => 'Si activas esto, los premios se mostraran solo una vez por usuario en los perfiles',
    'setting_ougc_awards_showInProfilePresets' => 'Máximo de Premios Preestablecidos en Perfiles',
    'setting_ougc_awards_showInProfilePresets_desc' => 'Escribe el número máximo de premios preestablecidos para mostrar en los perfiles.',
    'setting_ougc_awards_perPage' => 'Elementos Por Página',
    'setting_ougc_awards_perPage_desc' => 'Número máximo de elementos a mostrar por página o dentro de listados.',
    'setting_ougc_awards_perPageViewUsers' => 'Premios Por Página en el Modal de Vista',
    'setting_ougc_awards_perPageViewUsers_desc' => 'Número máximo de premios a mostrar por página en el modal de vista.',
    'setting_ougc_awards_groupsView' => 'Grupos de Vista',
    'setting_ougc_awards_groupsView_desc' => 'Grupos permitidos para ver la página de premios.',
    'setting_ougc_awards_groupsPresets' => 'Grupos Permitidos para Presets',
    'setting_ougc_awards_groupsPresets_desc' => 'Selecciona qué grupos pueden usar y crear presets.',
    'setting_ougc_awards_presetsMaximum' => 'Máximo de Presets',
    'setting_ougc_awards_presetsMaximum_desc' => 'Selecciona la cantidad máxima de presets que se pueden crear.',
    'setting_ougc_awards_groupsModerators' => 'Grupos de Moderadores',
    'setting_ougc_awards_groupsModerators_desc' => 'Grupos permitidos para gestionar premios.',
    'setting_ougc_awards_statsEnabled' => 'Habilitar Estadísticas',
    'setting_ougc_awards_statsEnabled_desc' => '¿Quieres habilitar los usuarios más otorgados y los últimos otorgados en la página de estadísticas?',
    'setting_ougc_awards_statsLatestGrants' => 'Últimos Otorgamientos en Estadísticas',
    'setting_ougc_awards_statsLatestGrants_desc' => 'Escribe el número máximo de últimos otorgamientos a mostrar en la página de estadísticas.',
    'setting_ougc_awards_notificationPrivateMessage' => 'Enviar PM',
    'setting_ougc_awards_notificationPrivateMessage_desc' => '¿Quieres enviar un PM a los usuarios al recibir un premio?',
    'setting_ougc_awards_grantDefaultVisibleStatus' => 'Estado Visible Predeterminado al Otorgar',
    'setting_ougc_awards_grantDefaultVisibleStatus_desc' => 'Selecciona el estado visible de los premios al otorgar premios a los usuarios. Si se establece en <code>No</code>, los usuarios deberán configurar sus premios como visibles en la página de clasificación desde la página Mis Premios.',
    'setting_ougc_awards_uploadPath' => 'Ruta de Subidas',
    'setting_ougc_awards_uploadPath_desc' => 'Escribe la ruta donde se subirán las imágenes de los premios.',
    'setting_ougc_awards_uploadDimensions' => 'Dimensiones de Subidas',
    'setting_ougc_awards_uploadDimensions_desc' => 'Escribe las dimensiones máximas para las imágenes de los premios. Predeterminado <code>32|32</code>.',
    'setting_ougc_awards_uploadSize' => 'Tamaño de Subidas',
    'setting_ougc_awards_uploadSize_desc' => 'Escribe el tamaño máximo en bytes para las imágenes de los premios. Predeterminado <code>50</code>.',
    'setting_ougc_awards_privateMessageSenderUserID' => 'ID de Usuario del Remitente del Mensaje Privado',
    'setting_ougc_awards_privateMessageSenderUserID_desc' => 'Selecciona el identificador de usuario (UID) que enviará los mensajes privados a los usuarios al recibir un premio. El valor predeterminado es <code>0</code>.',
    'setting_ougc_awards_groupsTasks' => 'Grupos de Vista de Tareas',
    'setting_ougc_awards_groupsTasks_desc' => 'Grupos permitidos para ver la página de tareas de premios.',
    'setting_ougc_awards_groupsMyAwards' => 'Grupos de Vista de Mis Premios',
    'setting_ougc_awards_groupsMyAwards_desc' => 'Grupos permitidos para ver la página Mis Premios.',
    'setting_ougc_awards_enableDvzStream' => 'Habilitar Integración de DVZ Stream',
    'setting_ougc_awards_enableDvzStream_desc' => 'Habilitar la integración de DVZ Stream para premios otorgados.',

    'ougcAwardsTaskRan' => 'La tarea de premios se ejecutó con éxito.',

    'ougcAwardsPluginLibrary' => 'Este plugin requiere <a href="{1}">PluginLibrary</a> versión {2} o posterior para ser subido a tu foro.'
];
