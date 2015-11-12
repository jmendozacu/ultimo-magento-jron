<?php

class Medma_Exportcms_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup{

	const CSV_NAME = 'cmspages.csv';
	const CSV_NAME_FOR_BLOCKS = 'cmsblocks.csv';
	const EXPORT_PROFILE_NAME = 'Export CMS Pages';
	const IMPORT_PROFILE_NAME = 'Import CMS Pages';
	const IMPORT_PROFILE_NAME_FOR_BLOCKS = 'Import CMS Pages For Blocks';
	const EXPORT_PROFILE_NAME_FOR_BLOCKS = 'Export CMS Pages For Blocks';

	protected $_importActionsXml = '<action type="dataflow/convert_adapter_io" method="load">
    <var name="type">file</var>
    <var name="path">var/import</var>
    <var name="filename"><![CDATA[cmspages.csv]]></var>
    <var name="format"><![CDATA[csv]]></var>
</action>
<action type="dataflow/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
    <var name="store"><![CDATA[0]]></var>
    <var name="number_of_records">1</var>
    <var name="decimal_separator"><![CDATA[.]]></var>
    <var name="adapter">exportcms/convert_adapter_cms</var>
    <var name="method">saveRow</var>
</action>';

	protected $_exportActionsXml = '<action type="exportcms/convert_adapter_cms" method="load">
    <var name="store"><![CDATA[0]]></var>
</action>
<action type="exportcms/convert_parser_cmsexport" method="unparse">
</action>
<action type="dataflow/convert_mapper_column" method="map">
</action>
<action type="dataflow/convert_parser_csv" method="unparse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
</action>
<action type="dataflow/convert_adapter_io" method="save">
    <var name="type">file</var>
    <var name="path">var/export</var>
    <var name="filename"><![CDATA[cmspages.csv]]></var>
</action>';

	protected $_profilesForPages = array(
		'import' => array(
			'name' => self::IMPORT_PROFILE_NAME,
			'actions_xml' => '<action type="dataflow/convert_adapter_io" method="load">
    <var name="type">file</var>
    <var name="path">var/import</var>
    <var name="filename"><![CDATA[cmspages.csv]]></var>
    <var name="format"><![CDATA[csv]]></var>
</action>
<action type="dataflow/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
    <var name="store"><![CDATA[0]]></var>
    <var name="number_of_records">1</var>
    <var name="decimal_separator"><![CDATA[.]]></var>
    <var name="adapter">exportcms/convert_adapter_cms</var>
    <var name="method">saveRow</var>
</action>',
			'direction' => 'import',
			//'created_at'=> $this->getTimeStamp(),
			//'updated_at'=> $this->getTimeStamp(),
			'store_id' => 0,
		),
		'export' => array(
			'name' => self::EXPORT_PROFILE_NAME,
			'actions_xml' => '<action type="exportcms/convert_adapter_cms" method="load">
    <var name="store"><![CDATA[0]]></var>
</action>
<action type="exportcms/convert_parser_cmsexport" method="unparse">
</action>
<action type="dataflow/convert_mapper_column" method="map">
</action>
<action type="dataflow/convert_parser_csv" method="unparse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
</action>
<action type="dataflow/convert_adapter_io" method="save">
    <var name="type">file</var>
    <var name="path">var/export</var>
    <var name="filename"><![CDATA[cmspages.csv]]></var>
</action>',
			'direction' => 'export',
			//'created_at'=> $this->getTimeStamp(),
			//'updated_at'=> $this->getTimeStamp(),
			'store_id' => 0,
		),
	);

	protected $_profilesForBlocks = array(
		'import' => array(
			'name' => self::IMPORT_PROFILE_NAME_FOR_BLOCKS,
			'actions_xml' => '<action type="dataflow/convert_adapter_io" method="load">
    <var name="type">file</var>
    <var name="path">var/import</var>
    <var name="filename"><![CDATA[cmsblocks.csv]]></var>
    <var name="format"><![CDATA[csv]]></var>
</action>
<action type="dataflow/convert_parser_csv" method="parse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
    <var name="store"><![CDATA[0]]></var>
    <var name="number_of_records">1</var>
    <var name="decimal_separator"><![CDATA[.]]></var>
    <var name="adapter">exportcms/convert_adapter_cmsblocks</var>
    <var name="method">saveRow</var>
</action>',
			'direction' => 'import',
			'store_id' => 0,
		),
		'export' => array(
			'name' => self::EXPORT_PROFILE_NAME_FOR_BLOCKS,
			'actions_xml' => '<action type="exportcms/convert_adapter_cmsblocks" method="load">
    <var name="store"><![CDATA[0]]></var>
</action>
<action type="exportcms/convert_parser_cmsblocksexport" method="unparse">
</action>
<action type="dataflow/convert_mapper_column" method="map">
</action>
<action type="dataflow/convert_parser_csv" method="unparse">
    <var name="delimiter"><![CDATA[,]]></var>
    <var name="enclose"><![CDATA["]]></var>
    <var name="fieldnames">true</var>
</action>
<action type="dataflow/convert_adapter_io" method="save">
    <var name="type">file</var>
    <var name="path">var/export</var>
    <var name="filename"><![CDATA[cmsblocks.csv]]></var>
</action>',
			'direction' => 'export',
			'store_id' => 0,
		),
	);
}
