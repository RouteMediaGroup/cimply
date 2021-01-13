<?php

/**
 * Klasse zur Erstellung der PDF Dialoge.
 *
 * @package    Core
 */
class DialogPDFCreator
{
    /**
     * @var string
     */
    private $workflowid;
    /**
     * @var string
     */
    private $processId;
    /**
     * @var string
     */
    private $dialogName;
    /**
     * @var string
     */
    private $dialogTitle;
    /**
     * @var string
     */
    private $processName;
    /**
     * @var string
     */
    private $processVersion;
    private $processInformation;
    private $language;
    private $processDetails;
    private $simulation;

    /**
     * @var Step
     */
    private $step;

    /**
     * P = Portrait, L = Landscape
     *
     * @var String
     */
    private $currentPDFFormat;

    /**
     * P = Portrait, L = Landscape
     *
     * @var String
     */
    private $currentElementFormat;

    /**
     * @var PDF
     */
    private $pdf;
    private $fontName = [];
    private $fontStyle = [];
    private $thousandsSeparator;
    private $decimalSeparator;
    private $dateFormat;
    private $currentField;

    /**
     * @param string $workflowid
     * @param int $simulation
     * @param string $dialogName
     * @param Step $step
     */
    public function __construct($workflowid, $simulation, $dialogName, Step $step = null)
    {
        if (!$dialogName) {
            throw new JobRouterException('Missing parameter: dialogName');
        }

        if (!$workflowid) {
            if ($step == null) {
                throw new JobRouterException('Missing parameter: workflowid');
            } else {
                $workflowid = $step->getWorkflowId();
            }
        }

        $this->workflowid = $workflowid;
        $this->dialogName = $dialogName;
        $this->simulation = $simulation;
        $this->step = $step;

        $this->fontName['Times'] = 'Times';
        $this->fontName['Arial'] = 'Arial';

        $this->fontStyle['B'] = 'B';
        $this->fontStyle['I'] = 'I';

        $this->thousandsSeparator = SettingsManager::getSetting('thousands_separator');
        $this->decimalSeparator = SettingsManager::getSetting('decimal_separator');
        $this->dateFormat = SettingsManager::getSetting('date_format');
    }

    /**
     * @param $outputFile
     * @param bool $process_information
     * @param string $dest
     *
     * @return string
     * @throws JobRouterException
     * @throws NoInstanceFoundException
     */
    public function create($outputFile, $process_information = false, $dest = 'F')
    {
        $jobDB = DBFactory::getJobDB();
        $this->processInformation = $process_information;

        // read step data
        $incident = new Incident('##wjp_internal##', '', '', '', $this->workflowid, $this->simulation);
        $this->processName = $incident->getProcessName();
        $this->processVersion = $incident->getVersion();

        if ($this->step == null) {
            $this->step = new Step($this->workflowid, $incident, 'view', '', true);
        }

        // get default process language
        $this->language = ProcessManager::getInstance($this->processName, $this->processVersion)->getDefaultLanguage();

        /** @var DataObject_DB_Dialog $doDialog */
        $doDialog = DataObjectManagerFactory::getManager('db')
            ->createInstance(
                'Dialog',
                [
                    'processName' => $this->processName,
                    'version' => $this->processVersion,
                    'dialog' => $this->dialogName,
                ]
            )
            ->getInstance();

        // read dialog label translation
        $dialogTranslation = TranslationManager_Process::getDialogTranslation(
            $this->processName,
            $this->processVersion,
            $this->dialogName
        );

        $this->dialogTitle = _utf8_decode($dialogTranslation['label'][$this->language]);

        $this->fontName['Times'] = 'Times';
        $this->fontName['Arial'] = 'Arial';
        $this->currentPDFFormat = 'P';

        $this->setFirstPage();

        $firstSection = true;
        $firstField = true;

        $doNotPrint = [
            'button',
            'hidden',
            'file',
            'alternatiff',
            'jobsap',
            'iframe',
            'attachment',
            'dw_show_document',
            'dwis_show_document',
            'dwwebclient',
            'dwwebclient_iframe',
            'dwwebclient_show_document',
            'dwwebclient_show_document_iframe',
        ];

        $printOnNextPage = [
            'sqltable',
            'subtableview',
        ];

        $sections = $doDialog->getSections();
        // show all sections one below the other
        foreach ($sections as $section) {

            $sectionLabel = html_entity_decode($section->getLabel($this->language));
            $newSection = true;

            $rows = $section->getRows();
            /** @var DataObject_XML_DialogElement_Row $row */
            foreach ($rows as $row) {
                $columns = $row->getColumns();

                /** @var DataObject_XML_DialogElement_Column $column */
                foreach ($columns as $column) {
                    $elements = $column->getElements();

                    /** @var DataObject_XML_DialogElement $field */
                    foreach ($elements as $field) {

                        $this->currentField = $field;

                        $type = $field->getProperty('type');

                        // Wenn für diesen Typ kein PDF Eintrag erzeugt werden soll, dann hier ABBRECHEN
                        if ($field->getProperty('hidden') === '1'
                            || in_array(StringUtility::toLower($type), $doNotPrint)) {
                            continue;
                        }

                        $label = html_entity_decode($field->getLabel($this->language));

                        if (StringUtility::toLower($type) == 'description') {
                            $label = $this->step->replaceValue($label);
                        }

                        $align = $field->getProperty('align');
                        $imageFile = $field->getProperty('imageFile');
                        $fieldValue = $field->getProperty('value');
                        $dbField = $field->getProperty('fieldName');
                        $width = $field->getProperty('width');
                        $height = $field->getProperty('height');
                        $includeTime = $field->getProperty('includeTime');

                        if (!$align) {
                            $align = 'left';
                        }

                        $this->pdf->SetAligns(
                            [
                                'L',
                                $this->getAlignmentForPDF($align),
                            ]
                        );

                        if (!in_array(StringUtility::toLower($type), $printOnNextPage) && $newSection) {
                            if (!$firstSection) {
                                $this->pdf->Cell(30, 5, '', 0, 1, 'L');
                            }
                            $this->pdf->group(_utf8_decode($sectionLabel));
                        }

                        // Wert aus Arbeitstabelle lesen
                        if ($dbField != '') {
                            $value = $this->step->getTableValue($dbField);
                        } else {
                            $value = $this->step->replaceValue($fieldValue);
                        }

                        $value = strip_tags($value, '<br>');
                        $label = strip_tags($label, '<br>');

                        if ($this->inputIsUnicode()) {
                            $charset = 'UTF-8';
                            $value = html_entity_decode($value, ENT_QUOTES, $charset);
                            $label = html_entity_decode($label, ENT_QUOTES, $charset);
                        } else {
                            $value = str_replace('&euro;', 'EUR', $value);
                            $label = str_replace('&euro;', 'EUR', $label);
                        }

                        switch (StringUtility::toLower($type)) {
                            case 'sqlcheckbox':
                            case 'checkbox':
                                $txtValue = '';
                                if ($value != '' && $value != '0') {
                                    $txtValue = 'X';
                                }
                                $this->currentElementFormat = 'P';
                                $this->checkAddNewPage($firstField);
                                $this->pdf->Row(
                                    [
                                        $label,
                                        $txtValue,
                                    ],
                                    0
                                );
                                break;
                            case 'date':
                                if ($includeTime) {
                                    $fullDateTime = true;
                                } else {
                                    $fullDateTime = false;
                                }

                                // Wert aus Arbeitstabelle lesen - Spezialfall für Date-Felder
                                if ($dbField != '') {
                                    $value = $this->step->getTableValue($dbField, false, false, $fullDateTime);
                                } else {
                                    $value = $this->step->replaceValue($fieldValue);
                                }

                                $value = strip_tags($value, '<br>');

                                if ($value != '') {
                                    $value = DateUtility::getDateTimestamp($value);
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $dateFormat = $field->getDateFormat();
                                    if ($dateFormat == 5) {
                                        $dateFormat = DateUtility::getCurrentDateFormat();
                                    }
                                    $value = DateUtility::getFormattedDate(
                                        $dateFormat,
                                        $value,
                                        $fullDateTime,
                                        true
                                    );
                                }

                                $this->currentElementFormat = 'P';
                                $this->checkAddNewPage($firstField);
                                $this->pdf->Row(
                                    [
                                        $label,
                                        $value,
                                    ],
                                    0
                                );
                                break;
                            case 'decimal':
                                if ($value != '') {
                                    $value = $this->formatDecimalValue($value);
                                }
                                $this->currentElementFormat = 'P';
                                $this->checkAddNewPage($firstField);
                                $this->pdf->Row(
                                    [
                                        $label,
                                        $value,
                                    ],
                                    0
                                );
                                break;
                            case 'image':
                                $this->currentElementFormat = 'P';
                                $this->checkAddNewPage($firstField);
                                $imageFile = Utility::getImageFilename(
                                    $imageFile,
                                    $this->processName,
                                    $this->processVersion,
                                    false
                                );
                                if ($imageFile) {
                                    $this->pdf->imageline($imageFile, $align);
                                } else {
                                    // ignore image if it can't be found
                                    Utility::log(
                                        'file',
                                        'send',
                                        new LogInfo(
                                            __METHOD__,
                                            'ERR',
                                            'Error: image file could not be found for PDF export: ' . $imageFile
                                        )
                                    );
                                }
                                break;
                            case 'radio':
                                $txtValue = '';
                                if ($value == $fieldValue) {
                                    $txtValue = 'X';
                                }
                                $this->currentElementFormat = 'P';
                                $this->checkAddNewPage($firstField);
                                $this->pdf->Row(
                                    [
                                        $label,
                                        $txtValue,
                                    ],
                                    0
                                );
                                break;
                            case 'sqltable':
                                /** @var DataObject_XML_DialogElement_SqlTable $field */
                                $connection = $field->getProperty('connection');
                                $strSql = $field->getProperty('sql');
                                $field->setCurrentLanguage($this->language);
                                $this->drawSQLTable(
                                    $connection,
                                    $strSql,
                                    $width,
                                    $height,
                                    $newSection,
                                    $sectionLabel,
                                    $firstSection,
                                    $firstField
                                );
                                break;
                            case 'subtableview':
                                $subtableView = $field->getProperty('view');
                                $this->drawSubtableView(
                                    $subtableView,
                                    $newSection,
                                    $sectionLabel,
                                    $firstSection,
                                    $firstField
                                );
                                break;
                            case 'text':
                                $this->currentElementFormat = 'P';
                                $this->checkAddNewPage($firstField);
                                $this->pdf->Row(
                                    [
                                        $label,
                                        $value,
                                    ],
                                    0
                                );
                                break;
                            case 'sqltextbox':
                                $this->currentElementFormat = 'P';
                                $this->checkAddNewPage($firstField);
                                if ($field->getProperty('fieldName') == '') {
                                    $connection = $field->getProperty('connection');
                                    $strSql = $field->getProperty('sql');
                                    $value = $this->loadSqlValue($connection, $strSql);
                                }
                                $this->pdf->Row(
                                    [
                                        $label,
                                        $value,
                                    ],
                                    0
                                );
                                break;
                            case 'description':
                                $this->currentElementFormat = 'P';
                                $this->checkAddNewPage($firstField);
                                $this->pdf->SetWidths(
                                    [
                                        180,
                                    ]
                                );
                                $this->pdf->Row(
                                    [
                                        $label,
                                    ],
                                    0
                                );
                                $this->pdf->SetWidths(
                                    [
                                        50,
                                        130,
                                    ]
                                );
                                break;
                            case 'blank':
                                $this->pdf->blank();
                                break;
                            default:
                                $this->currentElementFormat = 'P';
                                $this->checkAddNewPage($firstField);
                                $this->pdf->Row(
                                    [
                                        $label,
                                        $value,
                                    ],
                                    0
                                );
                                break;
                        }
                        $newSection = false;
                        $firstField = false;
                    }
                }
            }
            $firstSection = false;
        }

        if ($this->processDetails) {

            $this->pdf->AddPage('P');

            $this->pdf->SetFont($this->fontName['Arial'], 'B', 14, true);
            $this->pdf->Cell(10, 15, CONST_PROCESS_DETAILS, 0, 0, 'L');
            $this->pdf->SetFont($this->fontName['Arial'], 'B', 6, true);
            $this->pdf->SetXY(10, 27);
            $sizes = [
                15,
                50,
                35,
                30,
                25,
                25,
            ];
            $headers = [
                CONST_STEP,
                CONST_LABEL,
                CONST_JOBFUNCTION,
                CONST_USERNAME,
                CONST_INDATE,
                CONST_OUTDATE,
            ];
            $aligns = [
                "C",
                "C",
                "C",
                "C",
                "C",
                "C",
            ];
            $this->pdf->SetAligns($aligns);
            $this->pdf->SetWidths($sizes);
            $this->pdf->Row($headers, 0);
            $this->pdf->SetFont($this->fontName['Arial'], '', 6, true);

            if ($this->simulation) {
                $incidentsTableName = "JRINCIDENTSSIM";
            } else {
                $incidentsTableName = "JRINCIDENTS";
            }

            if ($this->processId) {

                $columnNamesToSelect = [
                    'step',
                    'steplabel',
                    'jobfunction',
                    'username',
                    'indate',
                    'outdate',
                ];

                $strSql = "SELECT " . implode(', ', $columnNamesToSelect) . " FROM " . $incidentsTableName
                    . " WHERE processid = " . $jobDB->quote($this->processId) . " ORDER BY indate, step";
                $result = $jobDB->query($strSql, new LogInfo(__METHOD__));
                if ($result === false) {
                    throw new JobRouterException($jobDB->getErrorMessage());
                }

                while ($row = $jobDB->fetchRow($result)) {
                    $data = [];
                    foreach ($columnNamesToSelect as $columnName) {
                        if ($columnName == 'indate' || $columnName == 'outdate') {
                            $data[] = substr($row[$columnName], 0, 19);
                        } elseif ($columnName == 'username') {
                            if ($row['workflowid'] == $this->step->getWorkflowId() && $row[$columnName] == '') {
                                $username = $this->step->getActiveUsername();
                            } else {
                                $username = $row[$columnName];
                            }
                            if ($username == '##wjp_internal##') {
                                $username = '';
                            }
                            $data[] = $username;
                        } else {
                            $data[] = $row[$columnName];
                        }
                    }
                    $this->pdf->Row($data, 0);
                }
            }

            $this->pdf->SetFont($this->fontName['Arial'], '', 9, true);
        }

        return $this->pdf->Output($outputFile, $dest);
    }

    /**
     * Get shortened alignment for use in PDF class
     * left => L
     * center => C
     * right => R
     *
     * @param string $align
     *
     * @return string
     */
    private function getAlignmentForPDF($align)
    {
        return strtoupper(substr($align, 0, 1));
    }

    /**
     * @throws JobRouterException
     */
    private function setFirstPage()
    {
        if ($this->inputIsUnicode()) {
            require_once __DIR__ . '/../../tcpdf/jrtcpdf.php';
            $this->pdf = new PDF();

            $this->pdf->setFontSubsetting(true);

            $this->fontName['Times'] = 'dejavusans';
            $this->fontName['Arial'] = 'dejavusans';

            $this->fontStyle['I'] = '';
            $this->fontStyle['B'] = '';

            $this->pdf->setCellPaddings('', 1, '', 1);
        } else {
            require_once __DIR__ . '/../../fpdf/jrpdf.php';
            $this->pdf = new PDF();
        }

        $this->pdf->AliasNbPages();
        $this->pdf->AddPage($this->currentPDFFormat);
        $this->pdf->SetFont($this->fontName['Times'], '', 12, true);

        $this->pdf->pdf_footer = '';
        $this->pdf->SetTitle('');

        // internal PDF infos
        $this->pdf->SetAuthor('JobRouter');
        $this->pdf->SetCreator('JobRouter');
        $this->pdf->SetSubject($this->processName);

        // show process information in PDF
        if ($this->processInformation) {
            $this->showProcessInformation();
        }

        // show dialog title in bold
        $this->pdf->SetFont($this->fontName['Arial'], $this->fontStyle['B'], 14, true);
        $this->pdf->Cell(10, 15, $this->dialogTitle, 0, 0, 'L');
        $this->pdf->SetFont($this->fontName['Arial'], $this->fontStyle['B'], 8, true);
        $this->pdf->SetFillColor(182, 182, 182);

        // set coordinates for the starting point of the dialog
        if ($this->processInformation) {
            $this->pdf->SetXY(10, 52);
        } else {
            $this->pdf->SetXY(10, 27);
        }
        $this->pdf->SetWidths(
            [
                50,
                130,
            ]
        );
    }

    /**
     * @throws JobRouterException
     */
    private function showProcessInformation()
    {

        $processTranslations = TranslationManager_Process::getProcessTranslations(
            $this->processName,
            $this->processVersion
        );
        $processDescription = _utf8_decode($processTranslations['process']['description'][$this->language]);

        $this->pdf->SetFont($this->fontName['Arial'], '', 9, true);
        $this->pdf->Cell(30, 5, CONST_PROCESSNAME . ':', 0, 0, 'L');
        $this->pdf->Cell(30, 5, $this->processName, 0, 1, 'L');
        $this->pdf->Cell(30, 5, CONST_VERSION . ':', 0, 0, 'L');
        $this->pdf->Cell(30, 5, $this->processVersion, 0, 1, 'L');
        $this->pdf->Cell(30, 5, CONST_LABEL . ':', 0, 0, 'L');
        $this->pdf->Cell(30, 5, $processDescription, 0, 1, 'L');
        $this->pdf->SetXY(10, 35);
    }

    private function formatDecimalValue($value)
    {
        $decimalPlaces = $this->determineDecimalPlaces($value);

        return Utility::getFormattedDecimal($value, $decimalPlaces, $this->thousandsSeparator, $this->decimalSeparator);
    }

    private function determineDecimalPlaces($value)
    {
        $decimalSeparatorPos = strrpos($value, '.');
        if ($decimalSeparatorPos === false) {
            return 0;
        }
        $decimalPlaces = substr($value, $decimalSeparatorPos + 1);

        return strlen($decimalPlaces);
    }

    /**
     * @param $connection
     * @param $strSql
     * @param $width
     * @param $height
     * @param $newSection
     * @param $sectionLabel
     * @param $firstSection
     * @param $firstField
     *
     * @throws JobRouterException
     */
    private function drawSQLTable(
        $connection,
        $strSql,
        $width,
        $height,
        $newSection,
        $sectionLabel,
        $firstSection,
        $firstField
    )
    {
        $resultData = $this->loadSqlTableData($connection, $strSql);
        $this->currentField->setStep($this->step);

        $width = floor($width / 2.75);
        $tsize = 0;

        $visibleColumnCount = 0;
        foreach ($resultData[0] as $columnName => $columnValue) {
            if (!$this->currentField->isColumnHidden($columnName)) {
                $visibleColumnCount++;
            }
        }

        if ($visibleColumnCount != 0) {

            if ($width == 0) {
                $col_width = 30;
            } else {
                $col_width = floor($width / $visibleColumnCount);
            }

            // Breite der Tabelle auslesen und header zusammenstellen
            $m = 0;
            foreach ($resultData[0] as $columnName => $columnValue) {
                if ($this->currentField->isColumnHidden($columnName)) {
                    continue;
                }

                $size = $col_width;
                $tsize += $size;
                if ($tsize > 275) {
                    break;
                }
                $sizes[] = $size;
                $headers[] = $this->resolveSQLTableColumnDisplayName($columnName);
                $aligns[] = 'L';
                ++$m;
            }

            if ($tsize > 180) {
                $remaining_width = 275 - ($col_width * $visibleColumnCount);
            } else {
                $remaining_width = 180 - ($col_width * $visibleColumnCount);
            }

            if ($remaining_width > 0) {
                $sizes[$m - 1] += $remaining_width;
            }
        }

        if ($tsize > 180) {
            $this->currentElementFormat = 'L';
            $this->checkAddNewPage($firstField);
        }

        if ($newSection) {
            if (!$firstSection && $this->currentPDFFormat != 'L') {
                $this->pdf->Cell(30, 5, '', 0, 1, 'L');
            }
            $sectionWidth = $this->currentPDFFormat != 'L' ? 180 : 275;
            $this->pdf->group(_utf8_decode($sectionLabel), $sectionWidth);
        }

        $font_size = SessionManager::getVar('pdf_subtable_font_size') ? SessionManager::getVar(
            'pdf_subtable_font_size'
        ) : '7';
        $this->pdf->SetFont($this->fontName['Arial'], '', $font_size, true);
        $this->pdf->SetLeftMargin(10);
        $this->pdf->SetAligns($aligns);
        $this->pdf->SetWidths($sizes);
        $this->pdf->Row($headers, 1);

        // GRL / 01.04.2011 / $resultData can be null
        if (is_array($resultData)) {
            foreach ($resultData as $row) {
                $data = [];
                foreach ($row as $columnName => $columnValue) {
                    if ($this->currentField->isColumnHidden($columnName)) {
                        continue;
                    }
                    if (!$columnValue && $columnValue != 0) {
                        $columnValue = ' ';
                    }
                    $data[] = _utf8_decode($columnValue);
                }
                $this->pdf->Row($data, 0);
            }
        }

        $this->pdf->SetFont($this->fontName['Arial'], '', 9, true);
        $naligns[] = 'L';
        $naligns[] = 'L';
        $this->pdf->SetAligns($naligns);
    }

    /**
     * @param string $columnName
     *
     * @return string
     * @throws JobRouterException
     */
    private function resolveSQLTableColumnDisplayName(string $columnName): string
    {
        /** @var DataObject_XML_DialogElement_SqlTable $table */
        $table = $this->currentField;
        $columns = $table->getRawHeaderData();
        foreach ($columns as $column) {
            if ($column['name'] !== $columnName) {
                continue;
            }

            return $table->getColumnLabel($column) ?: $columnName;
        }

        return $columnName;
    }

    private function loadSqlValue($connection, $strSql)
    {
        $jobDB = null;
        if ($connection != '') {
            try {
                $jobDB = DBFactory::getInstance($this->processName, $this->processVersion, $connection);
                // @codingStandardsIgnoreStart
            } catch (JobRouterException $e) {
                // Keine Verbindung zu externer Datenbank möglich
            }
            // @codingStandardsIgnoreEnd
        } else {
            $jobDB = DBFactory::getJobDB();
        }

        if (is_null($jobDB)) {
            return null;
        }

        $strSql = $this->step->replaceValue($strSql);

        $result = $jobDB->query($strSql, new LogInfo(__METHOD__));
        if ($result === false) {
            return null;
        }

        $columnValue = $jobDB->fetchOne($result);
        if ($columnValue == '') {
            return '';
        }

        return $columnValue;
    }

    private function loadSqlTableData($connection, $strSql)
    {
        $jobDB = null;
        if ($connection != '') {
            try {
                $jobDB = DBFactory::getInstance($this->processName, $this->processVersion, $connection);
            } catch (JobRouterException $e) {
                // Keine Verbindung zu externer Datenbank möglich
            }
        } else {
            $jobDB = DBFactory::getJobDB();
        }

        if (is_null($jobDB)) {
            return null;
        }

        $strSql = $this->step->replaceValue($strSql);

        $result = $jobDB->query($strSql, new LogInfo(__METHOD__));
        if ($result === false) {
            return null;
        }

        // #1036 Groß-/Kleinschreibung der Spaltenüberschriften in SQL_TABLE
        $columnNames = [];
        preg_match_all('| AS .[^,]*|ims', $strSql, $matches, PREG_PATTERN_ORDER);
        foreach ($matches[0] as $match) {
            $match = str_ireplace('AS', '', $match);
            if (($fromPos = stripos($match, 'FROM')) !== false) {
                $match = substr($match, 0, $fromPos);
            }
            $match = trim($match, " \r\n'\"");
            $columnNames[StringUtility::toLower($match)] = $match;
        }

        // #2901 / RAC / Fehler bei Umlauten in Spaltennamen behoben
        $GLOBALS['key_case_conversion_charset'] = $jobDB->getCharset() ? $jobDB->getCharset() : CONST_DB_CHARSET;

        $resultColumnNames = $jobDB->getColumnNames($result, true);
        if ($resultColumnNames === false) {
            return null;
        }

        unset($GLOBALS['key_case_conversion_charset']);

        foreach ($resultColumnNames as $columnName) {
            if ($columnNames[StringUtility::toLower($columnName)] == '') {
                $columnNames[$columnName] = _utf8_encode(
                    CharsetUtility::convertEncoding($columnName, CONST_DB_CHARSET, $jobDB->getCharset())
                );
            } else {
                $columnNames[$columnName] = _utf8_encode(
                    CharsetUtility::convertEncoding(
                        $columnNames[StringUtility::toLower($columnName)],
                        CONST_DB_CHARSET,
                        $jobDB->getCharset()
                    )
                );
            }
        }

        $encodedData = [];
        $rowNum = 0;
        while ($row = $jobDB->fetchRow($result)) {
            foreach ($row as $columnName => $columnValue) {
                // GRL / 07.07.2015 / Bug #5126 Umlauts will be displayed in table header correctly
                if (CONST_DB_SERVER == DBFactory::DB_SQLSRV) {
                    $columnName = utf8_encode($columnName);
                }
                $encodedData[$rowNum][$columnNames[$columnName]] = _utf8_encode($columnValue);
            }
            ++$rowNum;
        }

        // Return empty row, if no result data fond!
        if ($rowNum === 0) {
            foreach ($columnNames as $columnName) {
                $encodedData[0][$columnName] = '-';
            }
        }

        return $encodedData;
    }

    /**
     * @param $subtableViewName
     * @param $newSection
     * @param $sectionLabel
     * @param $firstSection
     * @param $firstField
     *
     * @throws JobRouterException
     * @throws NoInstanceFoundException
     */
    private function drawSubtableView(
        $subtableViewName,
        $newSection,
        $sectionLabel,
        $firstSection,
        $firstField
    )
    {
        /** @var DataObject_DB_SubtableView $subtableView */
        $subtableView = DataObjectManagerFactory::getManager('db')
            ->createInstance(
                'SubtableView',
                [
                    'processName' => $this->processName,
                    'version' => $this->processVersion,
                    'subtableView' => $subtableViewName,
                ]
            )
            ->getInstance();

        $fields = $subtableView->getElements();

        // Für diese Einträge soll kein PDF Eintrag erzeugt werden
        $doNotPrint = [
            'attachment',
            'button',
            'dw_show_document',
            'dwwebclient_show_document',
            'file',
            'hidden',
        ];

        $resultData = [];
        $resultHeaders = [];

        /** @var DataObject_XML_SubtableViewElement $field */
        foreach ($fields as $field) {

            $id = $field->getProperty('id');
            $type = $field->getProperty('type');

            // Wenn für diesen Typ kein PDF Eintrag erzeugt werden soll, dann hier abbrechen
            if ($field->getProperty('hidden') === '1' || in_array(StringUtility::toLower($type), $doNotPrint)) {
                continue;
            }

            $label = html_entity_decode($field->getLabel($this->language));

            $resultData[$id]['id'] = $id;
            $resultData[$id]['type'] = $type;
            $resultData[$id]['fieldName'] = $field->getProperty('fieldName');
            $resultData[$id]['align'] = $field->getProperty('align') == '' ? 'left' : $field->getProperty('align');
            $resultData[$id]['imageFile'] = $field->getProperty('imageFile');
            $resultData[$id]['fieldValue'] = $field->getProperty('value');
            $resultData[$id]['sum'] = $field->getProperty('sum');
            $resultData[$id]['sumFieldName'] = $field->getProperty('sumFieldName');
            $resultData[$id]['label'] = $label;
            $resultData[$id]['name'] = $field->getProperty('name');
            if (StringUtility::toLower($type) == 'date') {
                /** @noinspection PhpUndefinedMethodInspection */
                $dateFormat = $field->getDateFormat();
                if ($dateFormat == 5) {
                    $dateFormat = DateUtility::getCurrentDateFormat();
                }
                $resultData[$id]['format'] = $dateFormat;
            }

            $resultHeaders[$id]['label'] = $label;
            $resultHeaders[$id]['width'] = $field->getProperty('width');
            if ($resultHeaders[$id]['width'] == '') {
                switch (StringUtility::toLower($type)) {
                    case 'checkbox':
                    case 'sqlcheckbox':
                    case 'radio':
                        $resultHeaders[$id]['width'] = 10;
                        break;
                    case 'date':
                        $width = 18;
                        if ($field->getProperty('includeTime')) {
                            $width = 30;
                        }
                        $resultHeaders[$id]['width'] = $width;
                        break;
                    default:
                        $resultHeaders[$id]['width'] = 30;
                        break;
                }
            } else {
                $resultHeaders[$id]['width'] = $resultHeaders[$id]['width'] / 3;
            }
        }

        // Breite der Tabelle auslesen
        $tsize = 0;
        foreach ($resultHeaders as $key => $resultHeader) {
            $tsize += $resultHeader['width'];

            $sizes[] = $resultHeader['width'];
            $headers[] = $resultHeader['label'];
            $aligns[] = 'L';
            $displayedColumns[] = $key;
        }

        if ($tsize > 190) {
            $this->currentElementFormat = 'L';
            $this->checkAddNewPage($firstField);
        }

        if ($newSection) {
            if (!$firstSection && $this->currentPDFFormat != 'L') {
                $this->pdf->Cell(30, 5, '', 0, 1, 'L');
            }
            $sectionWidth = $this->currentPDFFormat != 'L' ? 180 : 275;
            $this->pdf->group(_utf8_decode($sectionLabel), $sectionWidth);
        }

        $font_size = SessionManager::getVar('pdf_subtable_font_size') ? SessionManager::getVar(
            'pdf_subtable_font_size'
        ) : '7';
        $this->pdf->SetFont($this->fontName['Arial'], '', $font_size, true);
        $this->pdf->SetLeftMargin(10);
        $this->pdf->SetAligns($aligns);
        $this->pdf->SetWidths($sizes);
        $this->pdf->Row($headers, 1);

        $subtableView->setStep($this->step);
        $subtableViewData = $subtableView->getData();

        if (count($subtableViewData) > 0) {

            $sumData = [];
            $blnSumFound = false;

            foreach ($subtableViewData as $stvElements) {

                $data = [];

                foreach ($resultData as $key => $field) {

                    if (!in_array($key, $displayedColumns)) {
                        continue;
                    }

                    $stvElement = $stvElements[$field['name']];
                    $value = $stvElement->getValue();

                    switch (StringUtility::toLower($field['type'])) {
                        case 'sqlcheckbox':
                        case 'checkbox':
                            $txtValue = '';
                            if ($value != '' && $value != '0') {
                                $txtValue = 'X';
                            }
                            $data[] = $txtValue;
                            break;
                        case 'date':
                            if ($value != '') {
                                if (!is_numeric($value)) {
                                    $value = DateUtility::getDateTimestamp($value);
                                }
                                $includeTime = false;
                                if ($stvElement instanceof DataObject_XML_SubtableViewElement_Date) {
                                    $includeTime = $stvElement->getIncludeTime();
                                }
                                $value = DateUtility::getFormattedDate($field['format'], $value, $includeTime, true);
                            }
                            $data[] = $value;
                            break;
                        case 'decimal':
                            if ($value != '') {
                                if ($field['sum'] && !$field['sumFieldName']) {
                                    if (!isset($sumData[$key])) {
                                        $sumData[$key] = 0;
                                    }
                                    $sumData[$key] += $value;
                                    $blnSumFound = true;
                                }
                                $value = $this->formatDecimalValue($value);
                            }
                            $data[] = $value;
                            break;
                        case 'image':
                            $data[] = '';
                            break;
                        case 'radio':
                            $txtValue = '';
                            if ($value == $field['fieldValue']) {
                                $txtValue = 'X';
                            }
                            $data[] = $txtValue;
                            break;
                        case 'textbox':
                        case 'posamount':
                            if ($value != '' && $field['sum'] && !$field['sumFieldName']) {
                                if (!isset($sumData[$key])) {
                                    $sumData[$key] = 0;
                                }
                                $sumData[$key] += $value;
                                $blnSumFound = true;
                            }
                            $data[] = $value;
                            break;
                        default:
                            $data[] = $value;
                            break;
                    }
                }

                $this->pdf->Row($data, 0);
            }

            foreach ($resultData as $key => $field) {

                if (!in_array($key, $displayedColumns)) {
                    continue;
                }

                if ($field['sum'] && $field['sumFieldName']) {
                    $sumData[$key] = $this->step->getTableValue($field['sumFieldName'], true, true);
                    $blnSumFound = true;
                } elseif ($field['sum'] && !$field['sumFieldName']) {
                    $sumData[$key] = $this->formatDecimalValue($sumData[$key]);
                } elseif (!$field['sum']) {
                    $sumData[$key] = '';
                }
            }

            if ($blnSumFound) {
                $sumData = array_values($sumData);
                $this->pdf->Row($sumData, 0);
            }
        } else {
            $data = [];
            foreach ($resultData as $key => $field) {
                if (!in_array($key, $displayedColumns)) {
                    continue;
                }
                $data[] = '-';
            }
            $this->pdf->Row($data, 0);
        }

        $this->pdf->SetFont($this->fontName['Arial'], '', 9, true);
        $naligns[] = 'L';
        $naligns[] = 'L';
        $this->pdf->SetAligns($naligns);
    }

    private function checkAddNewPage($firstField)
    {
        switch ($this->currentPDFFormat) {
            case 'L':
                if ($this->currentElementFormat == 'P') {
                    if (!$firstField) {
                        $this->pdf->AddPage('P');
                    } else {
                        $this->pdf->setPageOrientation('P');
                        if ($this->pdf->getPage() == 1) {
                            $this->currentPDFFormat = 'P';
                            $this->setFirstPage();
                        }
                    }
                    $this->currentPDFFormat = 'P';
                }
                break;
            case 'P':
                if ($this->currentElementFormat == 'L') {
                    if (!$firstField) {
                        $this->pdf->AddPage('L');
                    } else {
                        $this->pdf->setPageOrientation('L');
                        if ($this->pdf->getPage() == 1) {
                            $this->currentPDFFormat = 'L';
                            $this->setFirstPage();
                        }
                    }
                    $this->currentPDFFormat = 'L';
                } else {
                    $this->pdf->SetWidths(
                        [
                            50,
                            130,
                        ]
                    );
                }
                break;
            default:
                break;
        }
    }

    public function setProcessDetails($processDetails)
    {
        $this->processDetails = $processDetails;
    }

    public function setProcessId($processId)
    {
        $this->processId = $processId;
    }

    private function inputIsUnicode()
    {
        if (CONST_UNICODE_MODE) {
            return true;
        }

        if (CONST_DB_CHARSET == 'UTF-8' && CONST_DB_SERVER == 5) {
            return true;
        }

        return false;
    }

}