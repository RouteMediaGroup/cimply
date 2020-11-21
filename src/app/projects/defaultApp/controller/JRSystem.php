<?php

use JobRouter\Common\Converter\FileNameConverter;
use JobRouter\JobViewer\FileIdentifier;
use JobRouter\JobViewer\JobArchiveFileIdentifier;
use JobRouter\JobViewer\MetaDataManager;

class jobarchiveSystemActivity extends SystemActivity
{
    private $storage;

    /** @var JobArchive_Archive */
    private $archive;

    /** @var  JobArchive_DocumentRevision */
    private $documentRevision;
    private $storedFile;
    /** @var  JobArchive_DocumentRevisionManager */
    private $documentRevisionManager;
    private $useSubtableForClipping;
    private $maxResults;
    private $documentRevisionSearchFilters = [];
    private $filteredDocumentRevisionIds = [];

    const REVISION_STORE_TIMES = 'revisionStoreTimes';
    const ALREADY_STORED_REVISION_IDS = 'alreadyStoredRevisionIds';

    const FUNCTION_CHECK_DOCUMENTS = "checkDocuments";
    const FUNCTION_EXPORT_PDF = "exportPdf";

    /**
     * The constructor for the module.
     * Initializes the module id and the dialog language.
     *
     * @param Step $step
     */
    public function __construct(Step $step = null)
    {
        parent::__construct($step);

        $this->disableCastOfEmptyDecimalValue();
    }

    public function getActivityId()
    {
        return 'jobarchive';
    }

    public function getActivityType()
    {
        if ($this->getSelectedFunction() == static::FUNCTION_CHECK_DOCUMENTS || $this->getSelectedFunction(
            ) == static::FUNCTION_EXPORT_PDF) {
            return parent::ACTIVITY_TYPE_NON_PHP;
        }

        return parent::ACTIVITY_TYPE_PHP;
    }

    public function getActivityName()
    {
        return 'JobArchive';
    }

    /**
     * Returns the description for the module.
     *
     * @return string - description
     */
    public function getActivityDescription()
    {
        return CONST_ACT_JD_DESCRIPTION;
    }

    /**
     * Indicates if this activity is licensed or not.
     *
     * @return boolean - license
     */
    public function isLicensed()
    {
        return LicenseReader::getInstance()->isModuleValid(LicenseReader::MODULE_JOBARCHIVE);
    }

    /**
     * Returns the text for the current step status.
     *
     * @return string - status text
     */
    public function getStatusText()
    {
        switch ($this->getStepStatus()) {
            case Step::MODULE_STATUS_OPEN:
                return CONST_NOT_PROCESSED;
            case 1:
                return CONST_PROCESSED;
            case STEP::MODULE_STATUS_FINISHED:
                return CONST_SYSTEM_ACTIVITY_COMPLETED;
            default:
                return '';
        }
    }

    /**
     * Returns the xml that defines the structure of the module
     * dialog, i.e. the available functions and their input and output
     * parameters, configuration, simulation settings, etc...
     *
     * @return string - xml
     */
    protected function getDialogXml()
    {
        return "<?xml version='1.0' encoding='UTF-8'?>
            <jobrouterModuleSettings>
                <module name='" . $this->getActivityName()
            . "'>
                    <functions>
                        <function id='indexDocument' name='CONST_ACT_JD_INDEX_DOCUMENT' description='CONST_ACT_JD_INDEX_DOCUMENT_DESCRIPTION'>
                            <userdefined>
                                <udfield name='CONST_ACT_JD_JDARCHIVE' desc='' id='jdSourceArchive' type='list' required='yes'/>
                                <udfield name='CONST_ACT_JD_SUBTABLE' id='fixSubtable' desc='CONST_ACT_JD_SUBTABLE_DESC' type='list' />
                            </userdefined>
                            <inputParameters>
                                <list id='search' name='CONST_ACT_JD_SEARCH_FILTER' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='yes' fixed='yes' datatype='varchar' required='yes' min='1'/>
                                <list id='index' name='CONST_ACT_JD_INDEXATION' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='yes' fixed='yes' datatype='varchar' required='yes' min='1'/>
                                <field id='maxResults' name='CONST_ACT_JD_ARCHIVE_MAXRESULTS' desc='CONST_ACT_JD_ARCHIVE_MAXRESULTS_DESC' worktable='yes' subtable='no' fixed='yes' datatype='int' required='no' />
                            </inputParameters>
                            <outputParameters>
                                <field id='returnCode' name='CONST_ACT_RETURN_CODE' desc='' worktable='yes' subtable='no' fixed='no' datatype='int'/>
                                <field id='errorMessage' name='CONST_ACT_ERROR_MSG' desc='' worktable='yes' subtable='' fixed='no' datatype='varchar'/>
                            </outputParameters>
                        </function>
                        <function id='archiveDocument' name='CONST_ACT_JD_ARCHIVE_DOCUMENT' description='CONST_ACT_JD_ARCHIVE_DOCUMENT_DESCRIPTION' call=''>
                            <userdefined>
                                <udfield name='CONST_ACT_JD_JDARCHIVE' desc='' id='jdSourceArchive' type='list' required='yes'/>
                                <udfield name='CONST_ACT_JD_SUBTABLE' id='fixSubtable' desc='CONST_ACT_JD_SUBTABLE_DESC' type='list' />
                            </userdefined>
                            <inputParameters>
                                <field id='inputPath' name='CONST_ACT_JD_ARCHIVE_FILE' desc='' worktable='yes' subtable='yes' fixed='no' datatype='file' required='yes' />
                                <field id='deleteFile' name='CONST_ACT_JD_DELETE_FILE' desc='CONST_ACT_JD_DELETE_FILE_DESC' worktable='yes' subtable='yes' fixed='yes' datatype='varchar' texttype='checkbox' required='yes' />
                                <field id='baseRevision' name='CONST_ACT_JD_ARCHIVE_BASEREV' desc='CONST_ACT_JD_ARCHIVE_BASEREV_DESC' worktable='yes' subtable='yes' fixed='yes' datatype='varchar' required='no' />
                                <field id='deleteDate' name='CONST_ACT_JD_DELETE_DATE' desc='' worktable='yes' subtable='yes' fixed='yes' datatype='date' required='no' />
                                <list id='index' name='CONST_ACT_JD_INDEXATION' desc='' fix_subtable='no' udl='jdSourceArchive' worktable='yes' subtable='yes' fixed='yes' datatype='varchar' required='no' />
                            </inputParameters>
                            <outputParameters>
                                <field id='returnCode' name='CONST_ACT_RETURN_CODE' desc='' worktable='yes' subtable='yes' fixed='no' datatype='int'/>
                                <field id='errorMessage' name='CONST_ACT_ERROR_MSG' desc='' worktable='yes' subtable='yes' fixed='no' datatype='varchar'/>
                                <field id='docid' name='CONST_JOBARCHIVEID' desc='CONST_JOBARCHIVEID_DESC' worktable='yes' subtable='yes' fixed='no' datatype='varchar'/>
                            </outputParameters>
                        </function>
                        <function id='archivePdf' name='CONST_ACT_JD_ARCHIVE_PDF' description='CONST_ACT_JD_ARCHIVE_PDF_DESCRIPTION' call=''>
                            <userdefined>
                                <udfield name='CONST_ACT_JD_JDARCHIVE' id='jdSourceArchive' desc='' type='list' required='yes'/>
                                <udfield name='CONST_ACT_JD_PDF_DIALOG' id='dialogName' desc='CONST_ACT_JD_PDF_DIALOG_DESC' type='list' required='yes'/>
                            </userdefined>
                            <inputParameters>
                                <list id='index' name='CONST_ACT_JD_INDEXATION' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='yes' fixed='yes' datatype='varchar' required='no' />
                            </inputParameters>
                            <outputParameters>
                                <field id='returnCode' name='CONST_ACT_RETURN_CODE' desc='' worktable='yes' subtable='no' fixed='no' datatype='int'/>
                                <field id='errorMessage' name='CONST_ACT_ERROR_MSG' desc='' worktable='yes' subtable='' fixed='no' datatype='varchar'/>
                                <field id='docid' name='CONST_JOBARCHIVEID' desc='CONST_JOBARCHIVEID_DESC' worktable='yes' subtable='yes' fixed='no' datatype='varchar'/>
                                <list id='indexOutput' name='CONST_INDEX_DESTINATION' desc='CONST_INDEX_DESTINATION_DESC' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='no' fixed='no' datatype='varchar' required='yes'/>
                            </outputParameters>
                        </function>
                        <function id='exportToPath' name='CONST_ACT_JD_EXPORT_DOCUMENT_TO_PATH' description='CONST_ACT_JD_EXPORT_DOCUMENT_TO_PATH_DESCRIPTION' call=''>
                            <userdefined>
                                <udfield name='CONST_ACT_JD_JDARCHIVE' id='jdSourceArchive' desc='' type='list' required='yes'/>
                            </userdefined>
                            <inputParameters>
                                <field id='inputPath' name='CONST_ACT_JD_EXPORT_PATH' desc='' worktable='yes' subtable='no' fixed='yes' datatype='varchar' required='yes'/>
                                <field id='maxResults' name='CONST_ACT_JD_ARCHIVE_MAXRESULTS' desc='CONST_ACT_JD_ARCHIVE_MAXRESULTS_DESC' worktable='yes' subtable='yes' fixed='yes' datatype='int' required='no' />
                                <list id='search' name='CONST_ACT_JD_SEARCH_FILTER' desc='CONST_ACT_JD_SEARCH_FILTER_DESC' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='no' fixed='yes' datatype='varchar' required='yes' min='1'/>
                            </inputParameters>
                            <outputParameters>
                                <field id='outputPath' name='CONST_ACT_JD_EXPORT_FILE' desc='' worktable='yes' subtable='yes' fixed='no' datatype='varchar' required='yes'/>
                                <list id='indexOutput' name='CONST_INDEX_DESTINATION' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='yes' fixed='no' datatype='varchar' required='yes'/>
                                <field id='returnCode' name='CONST_ACT_RETURN_CODE' desc='' worktable='yes' subtable='no' fixed='no' datatype='int'/>
                                <field id='errorMessage' name='CONST_ACT_ERROR_MSG' desc='' worktable='yes' subtable='' fixed='no' datatype='varchar'/>
                            </outputParameters>
                        </function>
                        <function id='exportPdf' name='CONST_ACT_JD_EXPORT_PDF' description='CONST_ACT_JD_EXPORT_PDF_DESCRIPTION' call=''>
                            <userdefined>
                                <udfield name='CONST_ACT_JD_JDARCHIVE' id='jdSourceArchive' desc='' type='list' required='yes'/>
                            </userdefined>
                            <inputParameters>
                                <field id='inputPath' name='CONST_ACT_JD_EXPORT_PATH' desc='' worktable='yes' subtable='no' fixed='yes' datatype='varchar' />
                                <field id='maxResults' name='CONST_ACT_JD_ARCHIVE_MAXRESULTS' desc='CONST_ACT_JD_ARCHIVE_MAXRESULTS_DESC' worktable='yes' subtable='no' fixed='yes' datatype='int' required='no' />
                                <field id='withAnnotations' name='CONST_ACT_JD_WITH_ANNOTATIONS' desc='CONST_ACT_JD_WITH_ANNOTATIONS_DESC' worktable='yes' subtable='no' fixed='yes' datatype='varchar' texttype='checkbox'/>
                                <list id='search' name='CONST_ACT_JD_SEARCH_FILTER' desc='CONST_ACT_JD_SEARCH_FILTER_DESC' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='no' fixed='yes' datatype='varchar' required='yes' min='1'/>
                            </inputParameters>
                            <outputParameters>
                                <field id='outputPath' name='CONST_ACT_JD_EXPORT_FILE' desc='' worktable='yes' subtable='yes' fixed='no' datatype='file' required='yes'/>
                                <field id='returnCode' name='CONST_ACT_RETURN_CODE' desc='' worktable='yes' subtable='no' fixed='no' datatype='int'/>
                                <field id='errorMessage' name='CONST_ACT_ERROR_MSG' desc='' worktable='yes' subtable='' fixed='no' datatype='varchar'/>
                            </outputParameters>
                        </function>                        
                        <function id='clipDocument' name='CONST_ACT_JD_CLIP_ATTACHMENT' description='CONST_ACT_JD_CLIP_ATTACHMENT_DESCRIPTION'>
                            <userdefined>
                                <udfield name='CONST_ACT_JD_JDARCHIVE' desc='' id='jdSourceArchive' type='list' required='yes'/>
                                <udfield name='CONST_ACT_JD_SUBTABLE' id='fixSubtable' desc='CONST_ACT_JD_SUBTABLE_DESC' type='list' />
                            </userdefined>
                            <inputParameters>
                                <field id='inputPath' name='CONST_ACT_JD_ARCHIVE_FILE' desc='' worktable='yes' subtable='yes' fixed='no' datatype='file' required='yes' />
                                <field id='deleteFile' name='CONST_ACT_JD_DELETE_FILE' desc='CONST_ACT_JD_DELETE_FILE_DESC' worktable='yes' subtable='yes' fixed='yes' datatype='varchar' texttype='checkbox' required='yes' />
                                <field id='deleteDate' name='CONST_ACT_JD_DELETE_DATE' desc='' worktable='yes' subtable='yes' fixed='yes' datatype='date' required='no' />
                                <list id='search' name='CONST_ACT_JD_SEARCH_FILTER' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='yes' fixed='yes' datatype='varchar' required='yes' min='1'/>
                                <list id='index' name='CONST_ACT_JD_INDEXATION' desc='' fix_subtable='no' udl='jdSourceArchive' worktable='yes' subtable='yes' fixed='yes' datatype='varchar' />
                            </inputParameters>
                            <outputParameters>
                                <field id='returnCode' name='CONST_ACT_RETURN_CODE' desc='' worktable='yes' subtable='yes' fixed='no' datatype='int'/>
                                <field id='errorMessage' name='CONST_ACT_ERROR_MSG' desc='' worktable='yes' subtable='yes' fixed='no' datatype='varchar'/>
                                <field id='docid' name='CONST_JOBARCHIVEID' desc='CONST_JOBARCHIVEID_DESC' worktable='yes' subtable='yes' fixed='no' datatype='varchar'/>
                                <list id='indexOutput' name='CONST_INDEX_DESTINATION' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='no' fixed='no' datatype='varchar' required='yes'/>
                            </outputParameters>
                        </function>
                        <function id='clipPdf' name='CONST_ACT_JD_CLIP_PDF' description='CONST_ACT_JD_CLIP_PDF_DESCRIPTION'>
                            <userdefined>
                                <udfield name='CONST_ACT_JD_JDARCHIVE' id='jdSourceArchive' desc='' type='list' required='yes'/>
                                <udfield name='CONST_ACT_JD_PDF_DIALOG' id='dialogName' desc='CONST_ACT_JD_PDF_DIALOG_DESC' type='list' required='yes'/>
                            </userdefined>
                            <inputParameters>
                                <list id='search' name='CONST_ACT_JD_SEARCH_FILTER' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='yes' fixed='yes' datatype='varchar' required='yes' min='1'/>
                                <list id='index' name='CONST_ACT_JD_INDEXATION' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='yes' fixed='yes' datatype='varchar'/>
                            </inputParameters>
                            <outputParameters>
                                <field id='returnCode' name='CONST_ACT_RETURN_CODE' desc='' worktable='yes' subtable='no' fixed='no' datatype='int'/>
                                <field id='errorMessage' name='CONST_ACT_ERROR_MSG' desc='' worktable='yes' subtable='' fixed='no' datatype='varchar'/>
                                <field id='docid' name='CONST_JOBARCHIVEID' desc='CONST_JOBARCHIVEID_DESC' worktable='yes' subtable='yes' fixed='no' datatype='varchar'/>
                                <list id='indexOutput' name='CONST_INDEX_DESTINATION' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='no' fixed='no' datatype='varchar' required='yes'/>
                            </outputParameters>
                        </function>
                        <function id='readIndex' name='CONST_ACT_JD_READ_INDEX' description='CONST_ACT_JD_READ_INDEX_DESCRIPTION' call=''>
                            <userdefined>
                                <udfield name='CONST_ACT_JD_JDARCHIVE' id='jdSourceArchive' desc='' type='list' required='yes'/>
                            </userdefined>
                            <inputParameters>
                                <field id='maxResults' name='CONST_ACT_JD_ARCHIVE_MAXRESULTS' desc='CONST_ACT_JD_ARCHIVE_MAXRESULTS_DESC' worktable='yes' subtable='yes' fixed='yes' datatype='int' required='no' />
                                <list id='search' name='CONST_ACT_JD_SEARCH_FILTER' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='no' fixed='yes' datatype='varchar' required='yes' min='1'/>
                            </inputParameters>
                            <outputParameters>
                                <list id='indexOutput' name='CONST_INDEX_DESTINATION' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='no' fixed='no' datatype='varchar' required='yes'/>
                                <field id='returnCode' name='CONST_ACT_RETURN_CODE' desc='' worktable='yes' subtable='no' fixed='no' datatype='int'/>
                                <field id='errorMessage' name='CONST_ACT_ERROR_MSG' desc='' worktable='yes' subtable='' fixed='no' datatype='varchar'/>
                            </outputParameters>
                        </function>
                        <function id='deleteDocument' name='CONST_ACT_JD_DELETE_DOCUMENT' description='CONST_ACT_JD_DELETE_DOCUMENT_DESCRIPTION' call=''>
                            <userdefined>
                                <udfield name='CONST_ACT_JD_JDARCHIVE' desc='' id='jdSourceArchive' type='list' required='yes'/>
                            </userdefined>
                            <inputParameters>
                                <field id='maxResults' name='CONST_ACT_JD_ARCHIVE_MAXRESULTS' desc='CONST_ACT_JD_ARCHIVE_MAXRESULTS_DESC' worktable='yes' subtable='no' fixed='yes' datatype='int' required='no' />
                                <list id='search' name='CONST_SEARCH_FILTER' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='no' fixed='yes' datatype='varchar' required='yes' min='1'/>
                            </inputParameters>
                            <outputParameters>
                                <field id='returnCode' name='CONST_ACT_RETURN_CODE' desc='' worktable='yes' subtable='no' fixed='no' datatype='int'/>
                                <field id='errorMessage' name='CONST_ACT_ERROR_MSG' desc='' worktable='yes' subtable='no' fixed='no' datatype='varchar'/>
                                <list id='indexOutput' name='CONST_INDEX_DESTINATION' desc='' fix_subtable='' udl='jdSourceArchive' worktable='yes' subtable='no' fixed='no' datatype='varchar' required='yes'/>
                            </outputParameters>
                        </function>
                        <function id='checkDocuments' name='CONST_ACT_JD_CHECK_DOCUMENTS' description='CONST_ACT_JD_CHECK_DOCUMENTS_DESCRIPTION' call=''>
                            <userdefined>
                                <udfield name='CONST_ACT_JD_JDARCHIVE' desc='' id='jdSourceArchive' type='list' required='yes'/>
                            </userdefined>
                            <inputParameters>
                                <field id='maxResults' name='CONST_ACT_JD_ARCHIVE_MAXRESULTS' desc='CONST_ACT_JD_ARCHIVE_MAXRESULTS_DESC' worktable='yes' subtable='no' fixed='yes' datatype='int' required='no' />
                            </inputParameters>
                            <outputParameters>
                                <field id='returnCode' name='CONST_ACT_RETURN_CODE' desc='' worktable='yes' subtable='no' fixed='no' datatype='int'/>
                                <field id='errorMessage' name='CONST_ACT_ERROR_MSG' desc='' worktable='yes' subtable='no' fixed='no' datatype='varchar'/>
                                <field id='textProtocol' name='CONST_ACT_JD_TEXT_PROTOCOL' desc='CONST_ACT_JD_TEXT_PROTOCOL_DESC' worktable='yes' subtable='no' fixed='no' datatype='varchar' required='no'/>
                                <field id='xmlProtocol' name='CONST_ACT_JD_XML_PROTOCOL' desc='CONST_ACT_JD_XML_PROTOCOL_DESC' worktable='yes' subtable='no' fixed='no' datatype='file' required='no'/>
                                <field id='fileCount' name='CONST_ACT_JD_FILE_COUNT' desc='CONST_ACT_JD_FILE_COUNT_DESC' worktable='yes' subtable='no' fixed='no' datatype='int' required='no'/>
                                <field id='checkSuccessCount' name='CONST_ACT_JD_CHECK_SUCCESS_COUNT' desc='CONST_ACT_JD_CHECK_SUCCESS_COUNT_DESC' worktable='yes' subtable='no' fixed='no' datatype='int' required='no'/>
                                <field id='checkErrorCount' name='CONST_ACT_JD_CHECK_ERROR_COUNT' desc='CONST_ACT_JD_CHECK_ERROR_COUNT_DESC' worktable='yes' subtable='no' fixed='no' datatype='int' required='no'/>
                            </outputParameters>
                        </function>

                    </functions>
                    <simulation>
                        <behavior id='EXECUTE' name='CONST_ACT_SIM_EXECUTE' desc='CONST_ACT_SIM_EXECUTE_DESC' />
                        <behavior id='SUCCESS' name='CONST_ACT_SIM_SUCCESS' desc='CONST_ACT_SIM_SUCCESS_DESC' />
                        <behavior id='ERROR' name='CONST_ACT_SIM_ERROR' desc='CONST_ACT_SIM_ERROR_DESC' />
                    </simulation>
                </module>
            </jobrouterModuleSettings>";
    }

    public function execute()
    {
        $this->readSettingsToXpath();
        $this->initActivitySettingsFromXpath();
        $this->resetReturnCodeAndMessage();

        try {
            $this->executeSelectedFunctionOrSimulation();
        } catch (Throwable $e) {
            $this->setReturnCodeAndMessage('-1', $e->getMessage());
            Utility::log('file', 'jobarchive', new LogInfo(__METHOD__, 'ERROR', $e->getMessage()));
            Utility::log('file', 'jobarchive', new LogInfo(__METHOD__, 'DEBUG', $e->getTraceAsString()));
            throw $e;
        }

        return true;
    }

    protected function prepareRevisionManager()
    {
        $this->archive = JobArchive_ArchiveFactory::getInstance($this->getProperty('archiveTableName'));
        $this->storage = JobArchive_StorageFactory::createInstance($this->archive->getStorageId());
        $this->documentRevisionManager = new JobArchive_DocumentRevisionManager($this->archive);

        $this->documentRevisionManager->setProtocol(JobArchive_Protocol::getInstance());
        // disable protocol by default to enable it exactly where its needed
        $this->documentRevisionManager->disableProtocolLogging();
    }

    protected function addDocumentRevisionSearchFilter($fieldName, $fieldValue)
    {
        $this->documentRevisionSearchFilters[$fieldName] = $fieldValue;
    }

    protected function isSubtableUsed()
    {
        $subtableName = $this->getProperty('subtableName');

        if (empty($subtableName)) {
            return false;
        }

        // subtable check
        $xpathSourceTypes = $this->getSettingsXpath()->query('//@sourcetype');
        foreach ($xpathSourceTypes as $sourceTypeAttribute) {
            if ($sourceTypeAttribute->value == '2') {
                return true;
            }
        }

        return false;
    }

    protected function ensureDocumentRevisionIsLoaded()
    {
        if (!($this->documentRevision instanceof JobArchive_DocumentRevision)) {
            throw new JobRouterException(__METHOD__ . ': ' . CONST_SA_JOBARCHIVE_DOCUMENT_REVISION_NOT_LOADED);
        }
    }

    protected function storeDocumentRevision()
    {
        $this->documentRevision = null;

        $inputPath = $this->getProperty('inputPath');
        $inputFileLink = $this->getProperty('inputFileLink');

        if (!$this->isFileExtensionAllowed($inputFileLink)) {
            throw new JobRouterException(
                __METHOD__ . ': ' . CONST_ERROR_FILETYPE_NOT_ALLOWED
            );
        }

        $deleteFile = $this->getProperty('deleteFile');
        $baseRev = $this->getProperty('baseRev');
        $deleteDate = $this->getProperty('deleteDate');

        $alreadyStoredRevisionIds = $this->getSystemActivityVar(static::ALREADY_STORED_REVISION_IDS, []);
        $revisionStoreTimes = $this->getSystemActivityVar(static::REVISION_STORE_TIMES, []);

        if (!$baseRev) {
            $this->ensureMaxDocumentsNotReached();
        }

        if (isset($alreadyStoredRevisionIds[$inputPath])) {
            $revisionId = $alreadyStoredRevisionIds[$inputPath];
            $revisionStoreTimes[$revisionId] = time();
            $this->setSystemActivityVar(static::REVISION_STORE_TIMES, $revisionStoreTimes);

            $this->documentRevisionManager->setRetrieveExactRevisionWithGet(true);
            $this->documentRevision = $this->documentRevisionManager->get($revisionId);
            $this->documentRevisionManager->setRetrieveExactRevisionWithGet(false);

            return $revisionId;
        }

        $inputFile = new File_Filesystem($inputFileLink);

        if (!empty($baseRev)) {
            try {
                $this->documentRevision = $this->documentRevisionManager->get((int)$baseRev);
                $this->documentRevision->setFile($inputFile);
                $this->documentRevision->setOriginalFilename($this->getProperty('inputFileOriginalFilename'));
                $this->documentRevisionManager->loadNonEmptyPropertiesForRevision($this->documentRevision);
                $this->documentRevisionManager->loadKeywordsForRevision($this->documentRevision);
            } catch (Throwable $e) {
                // ignore failed loading of base revision if not exists
            }
        }

        if (is_null($this->documentRevision)) {
            $this->documentRevision = new JobArchive_DocumentRevision($inputFile);
        }

        if (!empty($deleteDate)) {
            try {
                $dateTime = new DateTime($deleteDate);
            } catch (Throwable $e) {
                throw new JobRouterException(CONST_WRONG_DATE_FORMAT . ': delete_date');
            }

            if ($dateTime < new DateTime('+2 minutes')) {
                throw new JobRouterException(
                    __METHOD__ . ': delete_date cannot be configured in the past or within the next 2 minutes'
                );
            }

            $this->documentRevision->setDeleteDate($dateTime->format('Y-m-d H:i:s'));
        }

        $this->documentRevision->setOriginalFilename($this->getProperty('inputFileOriginalFilename'));
        $this->documentRevision->setWorkflowId($this->step->getWorkflowId());
        $this->documentRevision->setTableName($this->archive->getTable()->getName());

        $initiator = $this->step->getIncident()->getInitiator();
        $this->documentRevision->setCreatedBy($initiator);
        $this->documentRevision->setModifiedBy($initiator);

        $indexData = $this->encryptIndexFieldValues($this->resolveDomNodeListToArray($this->getProperty('listNodes')));

        $this->setPropertiesForRevision($indexData);
        $this->documentRevision->loadKeywords($indexData, $this->documentRevisionManager);

        $this->documentRevisionManager->enableProtocolLogging();
        $this->createEventForJobArchiveProtocol(JobArchive_Protocol::UPDATE);

        // The documentRevisionManager uses DataObjects which need the user context to apply the user to the created_by column
        $lastUserName = Utility::getCurrentUserName();
        Utility::setCurrentUserName($initiator);
        $documentRevisionId = $this->documentRevisionManager->save($this->documentRevision);
        Utility::setCurrentUserName($lastUserName);

        $this->documentRevisionManager->disableProtocolLogging();

        if (!$documentRevisionId) {
            throw new JobRouterException(
                __METHOD__ . ': ' . $this->documentRevisionManager->getIndexFieldsFormattedErrorMessage()
            );
        }

        $this->documentRevision->setId($documentRevisionId);

        $alreadyStoredRevisionIds[$inputPath] = $documentRevisionId;
        $revisionStoreTimes[$documentRevisionId] = time();

        $this->setSystemActivityVar(static::ALREADY_STORED_REVISION_IDS, $alreadyStoredRevisionIds);
        $this->setSystemActivityVar(static::REVISION_STORE_TIMES, $revisionStoreTimes);

        if ($deleteFile) {

            $inputPathNode = $this->getProperty('inputPathNode');
            $fieldName = $inputPathNode->getAttribute('value');

            if ($this->isSubtableUsedForInputFile()) {
                $this->subtableFilesToDelete[] = [
                    'subtable' => $this->getCurrentSubtable(),
                    'rowId' => $this->getCurrentSubtableRowId(),
                    'fieldName' => $fieldName,
                ];
            } else {
                $this->processTableFileToDelete = $fieldName;
            }
        }
    }

    private function isSubtableUsedForInputFile()
    {
        $inputPathNode = $this->getProperty('inputPathNode');

        return $inputPathNode->getAttribute('sourcetype') == static::SOURCETYPE_SUBTABLE;
    }

    private function setPropertiesForRevision(array $indexData)
    {
        $archiveTable = $this->documentRevisionManager->getArchive()->getTable();
        $nonKeywordFieldNames = $archiveTable->getNonKeywordFieldNames();

        foreach ($nonKeywordFieldNames as $propertyName) {
            if (!isset($indexData[$propertyName])) {
                continue;
            }
            $indexValue = $indexData[$propertyName];
            $isSubtableValue = is_array($indexValue) && isset($indexValue[1]);
            if ($isSubtableValue) {
                $indexValue = $indexValue[1];
            }
            $this->documentRevision->setProperty($propertyName, $indexValue);
        }
    }

    protected function isFileStoreCompleted()
    {
        // disable logging to prevent redundant "READ" log entry after archiving document
        $jobArchiveProtocol = JobArchive_Protocol::getInstance();
        $jobArchiveProtocol->disableLogging();

        $revisionStoreTimes = $this->getSystemActivityVar(static::REVISION_STORE_TIMES);
        if (!is_array($revisionStoreTimes)) {
            $revisionStoreTimes = [];
        }
        $this->documentRevisionManager->setRetrieveExactRevisionWithGet(true);
        foreach ($revisionStoreTimes as $revisionId => $storeTime) {
            $this->documentRevision = $this->documentRevisionManager->get($revisionId);
            $this->storedFile = $this->documentRevision->getFile();
            if (!$this->storage->isFileStoreCompleted($this->storedFile)) {
                $this->checkForFileStoreErrors();

                return false;
            }
        }
        $this->documentRevisionManager->setRetrieveExactRevisionWithGet(false);

        return true;
    }

    protected function checkForFileStoreErrors()
    {
        $revisionStoreTimes = $this->getSystemActivityVar(static::REVISION_STORE_TIMES, []);

        $revisionId = $this->documentRevision->getId();

        if (!isset($revisionStoreTimes[$revisionId])) {
            throw new JobRouterException(__METHOD__ . ': Store time of revision is not set in system activity vars');
        }

        $fileChangeTime = $revisionStoreTimes[$revisionId];
        $currentTime = time();

        $maxSecondsDifference = 5 * 90;
        if ($fileChangeTime < $currentTime - $maxSecondsDifference) {
            throw new JobRouterException(
                __METHOD__ . ': Maximum time for storing a file and its verification exceeded'
            );
        }
    }

    protected function resolveMaxResults()
    {
        $maxResults = 0;
        if ($this->getProperty('maxResults') instanceof DOMNode) {
            $maxResults = (int)$this->resolveParameterValueByDOMNode($this->getProperty('maxResults'));
        }

        if ($maxResults <= 0) {
            $maxResults = 1;
        }
        $this->maxResults = $maxResults;
    }

    protected function storeIndexForDocumentRevision()
    {
        $this->documentRevisionManager->loadPropertiesForRevision($this->documentRevision);
        $this->documentRevisionManager->loadKeywordsForRevision($this->documentRevision);

        $mappingArray = [
            'baserevision_id' => 'getBaseRevisionId',
        ];

        foreach ($this->getProperty('listIndexOutputNodes') as $node) {
            $revisionPropertyName = $node->getAttribute('id');
            if (isset($mappingArray[$revisionPropertyName])) {
                $getterName = $mappingArray[$revisionPropertyName];
                $revisionPropertyValue = $this->documentRevision->$getterName();
            } else {
                try {
                    $revisionPropertyValue = $this->documentRevision->getProperty($revisionPropertyName);
                } catch (UnexpectedValueException $e) {
                    $keywordValues = $this->documentRevision->getKeyword($revisionPropertyName);
                    if (is_array($keywordValues)) {
                        $revisionPropertyValue = implode(',', $keywordValues);
                    }
                }
            }
            $this->storeParameterValueByDOMNode($node, $revisionPropertyValue);
        }
    }

    protected function loadExactDocumentRevisionById($revisionId)
    {
        $this->documentRevisionManager->setRetrieveExactRevisionWithGet(true);
        $this->documentRevision = $this->documentRevisionManager->get($revisionId);
        $this->documentRevisionManager->setRetrieveExactRevisionWithGet(false);
    }

    protected function checkFilteredDocumentRevisionIdsCount()
    {
        $this->resolveMaxResults();

        if (count($this->filteredDocumentRevisionIds) == 0) {
            throw new JobRouterException(__METHOD__ . ': No document found for given filter.');
        }

        if (count($this->filteredDocumentRevisionIds) > $this->maxResults) {
            throw new JobRouterException(__METHOD__ . ': More than one document found for given filter.');
        }
    }

    protected function findRevisionIdsByFilter()
    {
        $this->loadSearchFilters();
        $this->loadFilteredDocumentRevisionIds();
        $this->checkFilteredDocumentRevisionIdsCount();
    }

    protected function loadSearchFilters()
    {
        $listSearchFilters = $this->resolveDomNodeListToArray($this->getProperty('listSearchFilterNodes'));

        if (isset($listSearchFilters['baserevision_id'])) {
            $this->addDocumentRevisionSearchFilter('id', $listSearchFilters['baserevision_id']);

            return;
        }

        foreach ($listSearchFilters as $fieldName => $fieldValue) {
            $this->addDocumentRevisionSearchFilter($fieldName, $fieldValue);
        }
    }

    protected function loadFilteredDocumentRevisionIds()
    {

        $this->filteredDocumentRevisionIds = [];

        if (count($this->documentRevisionSearchFilters) == 0) {
            throw new JobRouterException(__METHOD__ . ': ' . CONST_SA_JOBARCHIVE_NO_SEARCH_FILTERS);
        }

        if (isset($this->documentRevisionSearchFilters['id'])) {
            $this->filteredDocumentRevisionIds = [
                $this->documentRevisionSearchFilters['id'],
            ];

            return;
        }

        $strSql = "SELECT documentrevision_id FROM " . $this->getProperty('archiveTableName')
            . " WHERE documentrevision_id IN (SELECT id FROM JRDOCUMENTREVISIONS WHERE "
            . JobArchive_DocumentRevisionManager::revisionIsNotDeletedClause() . " ) ";

        $archiveTable = $this->archive->getTable();
        $archiveTableFieldsAndTypes = $archiveTable->getFieldTypes();

        $archiveTableFieldsAndTypes['id'] = 'bigint';
        $archiveTableFieldsAndTypes['baserevision_id'] = 'bigint';

        $filterFieldsAndTypes = [];
        $filterFieldsAndValues = [];
        foreach ($this->documentRevisionSearchFilters as $fieldName => $fieldValue) {
            if (!isset($archiveTableFieldsAndTypes[$fieldName])) {
                throw new JobRouterException(
                    __METHOD__ . ': '
                    . str_replace('[fieldname]', $fieldName, CONST_SA_JOBARCHIVE_FIELD_NOT_IN_ARCHIVE_TABLE)
                );
            }

            $filterFieldsAndTypes[$fieldName] = DBUtility::JRTypeToMDB2Type($archiveTableFieldsAndTypes[$fieldName]);
            $filterFieldsAndValues[$fieldName] = _utf8_decode($fieldValue);
            $strSql .= ' AND ' . $fieldName . ' = :' . $fieldName . ' ';
        }

        $result = static::$jobDB->preparedSelect($strSql, $filterFieldsAndValues, array_values($filterFieldsAndTypes));
        while ($row = static::$jobDB->fetchRow($result)) {
            array_push($this->filteredDocumentRevisionIds, (int)$row['documentrevision_id']);
        }
        $result->free();
    }


    /* * ******************************************************************************************************
     *
     * index document
     *
     * ******************************************************************************************************
     */

    protected function indexDocument()
    {
        $this->indexDocumentLoadConfigPropertiesFromXpath();
        $this->prepareRevisionManager();

        if ($this->isSubtableUsed()) {
            $this->executeMethodForSubtable('indexDocumentExecute', $this->getProperty('subtableName'));
        } else {
            $this->ensureSubtableIsNotUsed();
            $this->indexDocumentExecute();
        }

        $this->markActivityAsCompleted();
    }

    protected function indexDocumentLoadConfigPropertiesFromXpath()
    {
        // ud parameters
        $this->setProperty(
            'archiveTableName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="jdSourceArchive"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );
        $this->setProperty(
            'subtableName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="fixSubtable"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );

        // input parameters
        $this->setProperty(
            'listSearchFilterNodes',
            $this->getSettingsXpath()->query('//module/function/inputParameters/list[@id="search"]/listfield')
        );
        $this->setProperty(
            'listIndexFieldNodes',
            $this->getSettingsXpath()->query('//module/function/inputParameters/list[@id="index"]/listfield')
        );
        $this->setProperty(
            'maxResults',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="maxResults"]')->item(0)
        );

        // output parameters
        $this->setProperty(
            'returnCodeNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="returnCode"]')->item(0)
        );
        $this->setProperty(
            'errorMessageNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="errorMessage"]')->item(0)
        );
        $this->setProperty(
            'docIdNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="docid"]')->item(0)
        );
    }

    protected function indexDocumentExecute()
    {
        $this->findRevisionIdsByFilter();
        $this->indexDocumentReindexDocumentRevisions();

        try {
            $this->storeParameterValueByDOMNode($this->getProperty('returnCodeNode'), '');
            $this->storeParameterValueByDOMNode($this->getProperty('errorMessageNode'), '');
        } catch (JobRouterException $e) {
            $this->storeParameterValueByDOMNode($this->getProperty('returnCodeNode'), '-1');
            $this->storeParameterValueByDOMNode($this->getProperty('errorMessageNode'), $e->getMessage());
        }
    }

    protected function indexDocumentReindexDocumentRevisions()
    {
        $listIndexValues = $this->encryptIndexFieldValues(
            $this->resolveDomNodeListToArray($this->getProperty('listIndexFieldNodes'))
        );

        foreach ($this->filteredDocumentRevisionIds as $revisionId) {
            $this->documentRevision = $this->documentRevisionManager->get($revisionId);
            $this->documentRevisionManager->loadNonEmptyPropertiesForRevision($this->documentRevision);
            $this->documentRevisionManager->loadKeywordsForRevision($this->documentRevision);
            $this->documentRevision->setWorkflowId($this->step->getWorkflowId());
            $this->documentRevision->loadKeywords($listIndexValues, $this->documentRevisionManager);
            $this->setPropertiesForRevision($listIndexValues);
            $this->documentRevisionManager->enableProtocolLogging();
            $this->createEventForJobArchiveProtocol(JobArchive_Protocol::UPDATE);
            $documentRevisionId = $this->documentRevisionManager->save($this->documentRevision);
            $this->documentRevisionManager->disableProtocolLogging();

            if (!$documentRevisionId) {
                throw new JobRouterException(
                    __METHOD__ . ': ' . $this->documentRevisionManager->getIndexFieldsFormattedErrorMessage()
                );
            }
        }
    }

    /* * ******************************************************************************************************
     *
     * archive document
     *
     * ******************************************************************************************************
     */

    protected function archiveDocument()
    {
        $this->archiveDocumentLoadConfigPropertiesFromXpath();
        $this->prepareRevisionManager();

        if ($this->isFirstExecution()) {
            if ($this->isSubtableUsed()) {
                $this->executeMethodForSubtable('archiveDocumentExecute', $this->getProperty('subtableName'));
            } else {
                $this->ensureSubtableIsNotUsed();
                $this->archiveDocumentExecute();
            }
            $this->markActivityAsPending();
        }

        if ($this->isFileStoreCompleted()) {
            $this->markActivityAsCompleted();
        }
    }

    private function ensureMaxDocumentsNotReached()
    {
        if (!JobArchive_DocumentRevisionManager::maxDocumentsReached()) {
            return;
        }

        $templateReplacements = [
            'maxNumberOfDocuments' => JobArchive_DocumentRevisionManager::getMaxNumberOfDocuments(),
        ];
        $reachedMessage = StringUtility::replaceTextVars(CONST_MAX_DOCUMENTS_REACHED, $templateReplacements);
        throw new JobRouterException($reachedMessage);
    }

    protected function archiveDocumentLoadConfigPropertiesFromXpath()
    {
        // ud parameters
        $this->setProperty(
            'archiveTableName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="jdSourceArchive"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );
        $this->setProperty(
            'subtableName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="fixSubtable"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );

        // input parameters
        $this->setProperty(
            'inputPathNode',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="inputPath"]')->item(0)
        );
        $this->setProperty(
            'deleteFileNode',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="deleteFile"]')->item(0)
        );
        $this->setProperty(
            'baseRevNode',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="baseRevision"]')->item(0)
        );
        $this->setProperty(
            'deleteDateNode',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="deleteDate"]')->item(0)
        );
        $this->setProperty(
            'listNodes',
            $this->getSettingsXpath()->query('//module/function/inputParameters/list[@id="index"]/listfield')
        );

        // output parameters
        $this->setProperty(
            'returnCodeNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="returnCode"]')->item(0)
        );
        $this->setProperty(
            'errorMessageNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="errorMessage"]')->item(0)
        );
        $this->setProperty(
            'docIdNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="docid"]')->item(0)
        );
    }

    protected function archiveDocumentExecute()
    {
        $this->archiveDocumentResolveRevisionParameters();

        if (!$this->fileToArchiveExists()) {
            return;
        }

        $this->storeDocumentRevision();
        $this->storeParameterValueByDOMNode($this->getProperty('returnCodeNode'), '');
        $this->storeParameterValueByDOMNode($this->getProperty('errorMessageNode'), '');

        $this->ensureDocumentRevisionIsLoaded();
        $this->copyFileMetaData();
        $this->storeOutputParameter('docid', $this->documentRevision->getId());
    }

    private function copyFileMetaData()
    {
        $sourceIdentifier = $this->determineSourceFileIdentifier();
        if (!$sourceIdentifier) {
            return;
        }

        $identifier = new JobArchiveFileIdentifier();
        $identifier->revisionId = $this->documentRevision->getId();
        $identifier->fileId = $this->documentRevision->getFileId();

        $resolver = new MetaDataManager(static::$jobDB);
        $metadata = $resolver->resolve($sourceIdentifier);
        $resolver->persist($identifier, $metadata);
    }

    private function determineSourceFileIdentifier(): ?FileIdentifier
    {
        $node = $this->getProperty('inputPathNode');
        if ((int)$node->getAttribute('sourcetype') !== SystemActivity::SOURCETYPE_TABLE) {
            return null;
        }

        $path = $this->resolveParameterValueByDOMNode($node);
        $identifier = new \JobRouter\JobViewer\AttachmentFileIdentifier();
        $identifier->path = dirname($path);

        return $identifier;
    }

    private function createEventForJobArchiveProtocol($eventType = null)
    {
        $jobArchiveProtocol = JobArchive_Protocol::getInstance();
        $jobArchiveProtocol->setEvent(DataObjectManagerFactory::getManager('db')->createInstance('ArchiveProtocol'));
        $jobArchiveProtocol->getEvent()->setEventSource(JobArchive_Protocol::SYSTEMACTIVITY);
        $jobArchiveProtocol->getEvent()->setEventId($this->step->getWorkflowId());
        $jobArchiveProtocol->getEvent()->setEventInfo($this->getEventInfo());
        $jobArchiveProtocol->getEvent()->setEventDate(DBFactory::getJobDB()->now());
        $jobArchiveProtocol->getEvent()->setUsername(STEP::SYSTEM_USERNAME);
        $jobArchiveProtocol->getEvent()->setEventType($eventType);
        $this->documentRevisionManager->setProtocol($jobArchiveProtocol);
    }

    private function getEventInfo()
    {
        $infoArray = [];
        $infoArray['processname'] = $this->step->getProcessName();
        $infoArray['version'] = $this->step->getVersion();
        $infoArray['step'] = $this->step->getStep();
        $infoArray['processid'] = $this->step->getProcessId();
        $infoArray['workflowid'] = $this->step->getWorkflowId();
        $infoArray['selectedFunctionId'] = $this->getSelectedFunctionId();
        if ($this->step->isSimulation()) {
            $infoArray['simulation'] = 'true';
        }

        return json_encode($infoArray);
    }

    private function fileToArchiveExists()
    {
        $inputPath = $this->getProperty('inputFileLink');

        if (file_exists($inputPath) && !is_dir($inputPath)) {
            return true;
        }

        // skip subtable row without attachment
        if ($this->isSubtableUsed()) {
            return false;
        }

        // raise exception if process table input file does not exist
        throw new JobRouterException(CONST_FILE_DOES_NOT_EXIST);
    }

    protected function archiveDocumentResolveRevisionParameters()
    {
        $inputPath = $this->resolveParameterValueByDOMNode($this->getProperty('inputPathNode'));
        $this->setProperty('inputFileOriginalFilename', basename($inputPath));

        $inputPath = (new FileNameConverter())->convertToPlatformEncoding($inputPath, CONST_PLATFORM_CHARSET);

        $this->setProperty('inputPath', $inputPath);
        $this->setProperty('inputFileLink', Utility::getFullUploadPath($inputPath));
        $this->setProperty('deleteFile', $this->resolveParameterValueByDOMNode($this->getProperty('deleteFileNode')));
        $this->setProperty('baseRev', $this->resolveParameterValueByDOMNode($this->getProperty('baseRevNode')));
        $this->setProperty('deleteDate', $this->resolveParameterValueByDOMNode($this->getProperty('deleteDateNode')));
    }

    /* * ******************************************************************************************************
     *
     * archive pdf protocol
     *
     * ******************************************************************************************************
     */

    protected function archivePdf()
    {
        $this->archivePdfLoadConfigPropertiesFromXpath();
        $this->prepareRevisionManager();

        if ($this->isFirstExecution()) {
            if ($this->isSubtableUsed()) {
                $this->executeMethodForSubtable('archivePdfExecute', $this->getProperty('subtableName'));
            } else {
                $this->ensureSubtableIsNotUsed();
                $this->archivePdfExecute();
            }
            $this->markActivityAsPending();
        }

        if ($this->isFileStoreCompleted()) {
            $this->markActivityAsCompleted();
        }
    }

    protected function archivePdfLoadConfigPropertiesFromXpath()
    {
        // ud parameters
        $this->setProperty(
            'archiveTableName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="jdSourceArchive"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );
        $this->setProperty(
            'dialogName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="dialogName"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );

        // input parameters
        $this->setProperty(
            'listNodes',
            $this->getSettingsXpath()->query('//module/function/inputParameters/list[@id="index"]/listfield')
        );

        // output parameters
        $this->setProperty(
            'returnCodeNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="returnCode"]')->item(0)
        );
        $this->setProperty(
            'errorMessageNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="errorMessage"]')->item(0)
        );
        $this->setProperty(
            'docIdNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="docid"]')->item(0)
        );
    }

    protected function archivePdfExecute()
    {
        $pdfCreator = new DialogPDFCreator(
            $this->step->getWorkflowId(), $this->step->isSimulation(),
            $this->getProperty('dialogName')
        );
        $inputPath = '/temp/jobarchive_archivepdf_' . md5(uniqid(time())) . '.pdf';
        $inputFileLink = File::formatPath(Utility::getFullOutputPath() . $inputPath);
        $pdfCreator->create($inputFileLink);

        $this->setProperty('inputPath', $inputPath);
        $this->setProperty('inputFileLink', $inputFileLink);
        $this->setProperty('deleteFile', false);
        $this->setProperty('baseRev', null);
        $this->setProperty('deleteDate', null);

        $this->storeDocumentRevision();
        $this->storeParameterValueByDOMNode($this->getProperty('docIdNode'), $this->documentRevision->getId());
        $this->unlinkFileIfExists($inputFileLink);
    }

    /* * ******************************************************************************************************
     *
     * delete document (mark as deleted)
     *
     * ******************************************************************************************************
     */

    protected function deleteDocument()
    {
        $this->deleteDocumentLoadConfigPropertiesFromXpath();
        $this->prepareRevisionManager();

        if ($this->isSubtableUsed()) {
            $this->executeMethodForSubtable('deleteDocumentExecute', $this->getProperty('subtableName'));
        } else {
            $this->ensureSubtableIsNotUsed();
            $this->deleteDocumentExecute();
        }
        $this->markActivityAsCompleted();
    }

    protected function deleteDocumentLoadConfigPropertiesFromXpath()
    {
        // ud parameters
        $this->setProperty(
            'archiveTableName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="jdSourceArchive"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );

        // input parameters
        $this->setProperty(
            'listSearchFilterNodes',
            $this->getSettingsXpath()->query('//module/function/inputParameters/list[@id="search"]/listfield')
        );
        $this->setProperty(
            'maxResults',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="maxResults"]')->item(0)
        );

        // output parameters
        $this->setProperty(
            'returnCodeNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="returnCode"]')->item(0)
        );
        $this->setProperty(
            'errorMessageNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="errorMessage"]')->item(0)
        );
        $this->setProperty(
            'listIndexOutputNodes',
            $this->getSettingsXpath()->query('//module/function/outputParameters/list[@id="indexOutput"]/listfield')
        );
    }

    protected function deleteDocumentExecute()
    {
        $this->findRevisionIdsByFilter();

        foreach ($this->filteredDocumentRevisionIds as $revisionId) {
            $this->loadExactDocumentRevisionById($revisionId);
            $this->storeIndexForDocumentRevision();
            $this->documentRevisionManager->setRetrieveExactRevisionWithGet(true);

            $this->documentRevisionManager->enableProtocolLogging();
            $this->createEventForJobArchiveProtocol(JobArchive_Protocol::DELETE);
            $this->documentRevisionManager->delete($revisionId);
            $this->documentRevisionManager->disableProtocolLogging();

            $this->documentRevisionManager->setRetrieveExactRevisionWithGet(false);
        }

        $this->storeParameterValueByDOMNode($this->getProperty('returnCodeNode'), '');
        $this->storeParameterValueByDOMNode($this->getProperty('errorMessageNode'), '');
    }

    /* * ******************************************************************************************************
     *
     * read index
     *
     * ******************************************************************************************************
     */

    protected function readIndex()
    {
        $this->readIndexLoadConfigPropertiesFromXpath();
        $this->prepareRevisionManager();

        if ($this->isSubtableUsed()) {
            $this->executeMethodForSubtable('readIndexExecute', $this->getProperty('subtableName'));
        } else {
            $this->ensureSubtableIsNotUsed();
            $this->readIndexExecute();
        }
        $this->markActivityAsCompleted();
    }

    protected function readIndexLoadConfigPropertiesFromXpath()
    {
        // ud parameters
        $this->setProperty(
            'archiveTableName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="jdSourceArchive"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );

        // input parameters
        $this->setProperty(
            'listSearchFilterNodes',
            $this->getSettingsXpath()->query('//module/function/inputParameters/list[@id="search"]/listfield')
        );
        $this->setProperty(
            'maxResults',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="maxResults"]')->item(0)
        );

        // output parameters
        $this->setProperty(
            'returnCodeNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="returnCode"]')->item(0)
        );
        $this->setProperty(
            'errorMessageNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="errorMessage"]')->item(0)
        );
        $this->setProperty(
            'listIndexOutputNodes',
            $this->getSettingsXpath()->query('//module/function/outputParameters/list[@id="indexOutput"]/listfield')
        );
    }

    protected function readIndexExecute()
    {
        $this->findRevisionIdsByFilter();

        foreach ($this->filteredDocumentRevisionIds as $revisionId) {
            $this->documentRevisionManager->enableProtocolLogging();
            $this->loadExactDocumentRevisionById($revisionId);
            $this->documentRevisionManager->disableProtocolLogging();
            $this->storeIndexForDocumentRevision();
        }

        $this->storeParameterValueByDOMNode($this->getProperty('returnCodeNode'), '');
        $this->storeParameterValueByDOMNode($this->getProperty('errorMessageNode'), '');
    }

    /* * ******************************************************************************************************
     *
     * export documentfile to path
     *
     * ******************************************************************************************************
     */

    protected function exportToPath()
    {
        $this->exportToPathLoadConfigPropertiesFromXpath();
        $this->prepareRevisionManager();

        if ($this->isSubtableUsed()) {
            $this->executeMethodForSubtable('exportToPathExecute', $this->getProperty('subtableName'));
        } else {
            $this->ensureSubtableIsNotUsed();
            $this->exportToPathExecute();
        }
        $this->markActivityAsCompleted();
    }

    protected function exportToPathLoadConfigPropertiesFromXpath()
    {
        // ud parameters
        $this->setProperty(
            'archiveTableName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="jdSourceArchive"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );

        // input parameters
        $this->setProperty(
            'inputPathNode',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="inputPath"]')->item(0)
        );
        $this->setProperty(
            'listSearchFilterNodes',
            $this->getSettingsXpath()->query('//module/function/inputParameters/list[@id="search"]/listfield')
        );
        $this->setProperty(
            'maxResults',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="maxResults"]')->item(0)
        );

        // output parameters
        $this->setProperty(
            'outputPathNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="outputPath"]')->item(0)
        );
        $this->setProperty(
            'returnCodeNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="returnCode"]')->item(0)
        );
        $this->setProperty(
            'errorMessageNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="errorMessage"]')->item(0)
        );
        $this->setProperty(
            'listIndexOutputNodes',
            $this->getSettingsXpath()->query('//module/function/outputParameters/list[@id="indexOutput"]/listfield')
        );
    }

    protected function exportToPathExecute()
    {
        $this->findRevisionIdsByFilter();

        foreach ($this->filteredDocumentRevisionIds as $revisionId) {
            $this->documentRevisionManager->enableProtocolLogging();
            $this->createEventForJobArchiveProtocol(JobArchive_Protocol::DOWNLOAD);
            $this->loadExactDocumentRevisionById($revisionId);
            $this->storeIndexForDocumentRevision();
            $this->exportToPathExportCurrentRevisionFile();
            $this->documentRevisionManager->disableProtocolLogging();
        }

        $this->storeParameterValueByDOMNode($this->getProperty('returnCodeNode'), '');
        $this->storeParameterValueByDOMNode($this->getProperty('errorMessageNode'), '');
    }

    protected function exportToPathExportCurrentRevisionFile()
    {
        $fileNameConverter = new FileNameConverter();

        $inputPath = $this->resolveParameterValueByDOMNode($this->getProperty('inputPathNode'));
        $inputPath = $fileNameConverter->convertToPlatformEncoding($inputPath, CONST_PLATFORM_CHARSET);
        $outputPath = File::formatPath(
            $inputPath . '/' . $fileNameConverter->convertToPlatformEncoding(
                $this->documentRevision->getOriginalFilename(),
                $this->targetEncoding
            )
        );

        if ($this->documentRevisionManager->hasClippedFiles($this->documentRevision)) {
            $zipFilename = $fileNameConverter->convertToPlatformEncoding(
                $this->documentRevision->getOriginalFilename(),
                CONST_PLATFORM_CHARSET
            );
            $zipFilename .= '.zip';
            $zipPath = Utility::getFullTempPath() . DIRECTORY_SEPARATOR . $zipFilename;
            $this->ensureTemporaryZipFileNotExists($zipPath);
            $this->documentRevisionManager->createZipForRevision($zipPath, $this->documentRevision);
            $outputPath .= '.zip';
            $this->ensureOutputZipFileNotExists($outputPath);
            $this->copyZipToOutputPath($zipPath, $outputPath);
            $this->unlinkFileIfExists($zipPath);
        } else {
            $file = $this->documentRevision->getFile();
            if (false === file_put_contents($outputPath, $file->getData())) {
                throw new JobRouterException(
                    __METHOD__ . ': Could not store file ' . $this->documentRevision->getOriginalFilename()
                );
            }
        }

        $this->storeParameterValueByDOMNode($this->getProperty('outputPathNode'), $outputPath);
    }

    private function ensureTemporaryZipFileNotExists($zipPath)
    {
        if ($this->unlinkFileIfExists($zipPath)) {
            return;
        }

        throw new JobRouterException(__METHOD__ . ': Could not delete already existing temporary ZIP file.');
    }

    private function unlinkFileIfExists($filePath)
    {
        if (file_exists($filePath) && !unlink($filePath)) {
            return false;
        }

        return true;
    }

    private function ensureOutputZipFileNotExists($zipPath)
    {
        if ($this->unlinkFileIfExists($zipPath)) {
            return;
        }

        throw new JobRouterException(__METHOD__ . ': Could not delete already existing target ZIP file.');
    }

    private function copyZipToOutputPath($zipPath, $outputPath)
    {
        if (copy($zipPath, $outputPath)) {
            return;
        }

        throw new JobRouterException(__METHOD__ . ': Could not copy ZIP file to output path.');
    }

    /* * ******************************************************************************************************
     *
     * clipDocument
     *
     * ******************************************************************************************************
     */

    protected function clipDocument()
    {
        $this->clipDocumentLoadConfigPropertiesFromXpath();
        $this->prepareRevisionManager();

        if ($this->isFirstExecution()) {
            if ($this->isSubtableUsed()) {
                $this->executeMethodForSubtable('clipDocumentExecute', $this->getProperty('subtableName'));
            } else {
                $this->ensureSubtableIsNotUsed();
                $this->clipDocumentExecute();
            }
            $this->markActivityAsPending();
        }

        if ($this->isFileStoreCompleted()) {
            $this->markActivityAsCompleted();
        }
    }

    protected function clipDocumentLoadConfigPropertiesFromXpath()
    {
        // ud parameters
        $this->setProperty(
            'archiveTableName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="jdSourceArchive"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );
        $this->setProperty(
            'subtableName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="fixSubtable"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );

        // input parameters
        $this->setProperty(
            'inputPathNode',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="inputPath"]')->item(0)
        );
        $this->setProperty(
            'deleteFileNode',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="deleteFile"]')->item(0)
        );
        $this->setProperty(
            'deleteDateNode',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="deleteDate"]')->item(0)
        );
        $this->setProperty(
            'listIndexFieldNodes',
            $this->getSettingsXpath()->query('//module/function/inputParameters/list[@id="index"]/listfield')
        );
        $this->setProperty(
            'listSearchFilterNodes',
            $this->getSettingsXpath()->query('//module/function/inputParameters/list[@id="search"]/listfield')
        );

        // output parameters
        $this->setProperty(
            'returnCodeNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="returnCode"]')->item(0)
        );
        $this->setProperty(
            'errorMessageNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="errorMessage"]')->item(0)
        );
        $this->setProperty(
            'docIdNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="docid"]')->item(0)
        );
    }

    protected function initClipping()
    {
        try {
            $this->useSubtableForClipping = false;
            $this->documentRevision = $this->cloneRevisionForClipping();
        } catch (Throwable $e) {
            $this->useSubtableForClipping = true;
        }
    }

    protected function cloneRevisionForClipping()
    {
        $this->maxResults = 1;
        $this->findRevisionIdsByFilter();

        $listIndexValues = $this->encryptIndexFieldValues(
            $this->resolveDomNodeListToArray($this->getProperty('listIndexFieldNodes'))
        );

        foreach ($this->filteredDocumentRevisionIds as $revisionId) {

            $currentRevision = $this->documentRevisionManager->getLatestRevisionForId($revisionId);

            $nextRevision = $this->documentRevisionManager->cloneRevision($currentRevision);

            $nextRevision->setTableName($this->archive->getTable()->getName());
            $nextRevision->loadKeywords($listIndexValues, $this->documentRevisionManager);

            $nextRevision->setPropertiesFromInput($listIndexValues);
            $nextRevision->setWorkflowId($this->step->getWorkflowId());
            $nextRevision->setBaseRevisionId($currentRevision->getBaseRevisionId());

            $this->documentRevisionManager->enableProtocolLogging();
            $this->createEventForJobArchiveProtocol(JobArchive_Protocol::UPDATE);
            $documentRevisionId = $this->documentRevisionManager->save($nextRevision);
            $this->documentRevisionManager->disableProtocolLogging();

            if (!$documentRevisionId) {
                throw new JobRouterException(
                    __METHOD__ . ': ' . $this->documentRevisionManager->getIndexFieldsFormattedErrorMessage()
                );
            }

            $this->documentRevisionManager->cloneRevisionClippedFiles($currentRevision, $nextRevision);
        }

        return $nextRevision;
    }

    protected function clipDocumentExecute()
    {
        $inputPath = (new FileNameConverter())->convertToPlatformEncoding(
            $this->resolveInputParameter('inputPath'),
            CONST_PLATFORM_CHARSET
        );
        $fileToClip = Utility::getFullUploadPath() . DIRECTORY_SEPARATOR . $inputPath;

        if (!$this->fileToClipExists($fileToClip)) {
            return;
        }

        if (!$this->isFileExtensionAllowed($inputPath)) {
            throw new JobRouterException(
                __METHOD__ . ': ' . CONST_ERROR_FILETYPE_NOT_ALLOWED
            );
        }

        $this->documentRevision = $this->cloneRevisionForClipping();

        $this->createEventForJobArchiveProtocol(JobArchive_Protocol::UPDATE);

        $originalFilename = basename($this->resolveInputParameter('inputPath'));

        $file = new File_Filesystem($fileToClip);
        $this->documentRevisionManager->clipFile($this->documentRevision, $file, $originalFilename);

        if ($this->hasDeleteDate()) {
            $this->documentRevisionManager->updateDeleteDateForRevisionTree(
                $this->documentRevision->getId(),
                $this->getDeleteDate()
            );
        }

        $this->storeOutputParameter('returnCode', '');
        $this->storeOutputParameter('errorMessage', '');

        if ($this->documentRevision instanceof JobArchive_DocumentRevision) {
            $this->storeOutputParameter('docid', $this->documentRevision->getId());
        }

        try {
            $deleteFile = $this->resolveInputParameter('deleteFile');
        } catch (JobRouterException $e) {
            $deleteFile = false;
        }

        if ($deleteFile) {
            $inputPathNode = $this->getProperty('inputPathNode');
            $fieldName = $inputPathNode->getAttribute('value');

            if ($this->isSubtableUsedForInputFile()) {
                $this->deleteSubtableFile($fieldName);
            } else {
                $this->deleteFile($fieldName);
            }
        }
    }

    /**
     * @param string $fileToClip
     *
     * @return bool
     * @throws JobRouterException
     */
    protected function fileToClipExists($fileToClip)
    {
        if (file_exists($fileToClip) && !is_dir($fileToClip)) {
            return true;
        }

        // skip subtable row without attachment
        if ($this->isSubtableUsed()) {
            return false;
        }

        // raise error if clipped file is read from processtable but doesn't exist
        throw new JobRouterException(CONST_FILE_DOES_NOT_EXIST);
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    private function isFileExtensionAllowed($file)
    {
        $fileExtensionValidator = new \StringValidator_AllowedFileExtension();

        // check file extension
        if (!$fileExtensionValidator->validate($file)) {
            return false;
        }

        return true;
    }

    private function hasDeleteDate()
    {
        try {
            $deleteDate = $this->resolveInputParameter('deleteDate');
        } catch (Throwable $e) {
            return false;
        }

        if (empty($deleteDate)) {
            return false;
        }

        return true;
    }

    private function getDeleteDate()
    {
        return new DateTime($this->resolveInputParameter('deleteDate'));
    }

    /* * ******************************************************************************************************
     *
     * clipPdf
     *
     * ******************************************************************************************************
     */

    protected function clipPdf()
    {
        $this->clipPdfLoadConfigPropertiesFromXpath();
        $this->prepareRevisionManager();
        $this->initClipping();

        if ($this->isFirstExecution()) {
            if ($this->isSubtableUsed()) {
                $this->executeMethodForSubtable('clipPdfExecute', $this->getProperty('subtableName'));
            } else {
                $this->ensureSubtableIsNotUsed();
                $this->clipPdfExecute();
            }
            $this->markActivityAsPending();
        }

        if ($this->isFileStoreCompleted()) {
            $this->markActivityAsCompleted();
        }
    }

    protected function clipPdfLoadConfigPropertiesFromXpath()
    {
        // ud parameters
        $this->setProperty(
            'archiveTableName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="jdSourceArchive"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );

        $this->setProperty(
            'dialogName',
            $this->getSettingsXpath()
                ->query('//module/function/userdefined/udfield[@id="dialogName"]')
                ->item(0)
                ->getAttribute(
                    'value'
                )
        );

        // input parameters
        $this->setProperty(
            'deleteDateNode',
            $this->getSettingsXpath()->query('//module/function/inputParameters/field[@id="deleteDate"]')->item(0)
        );
        $this->setProperty(
            'listIndexFieldNodes',
            $this->getSettingsXpath()->query('//module/function/inputParameters/list[@id="index"]/listfield')
        );
        $this->setProperty(
            'listSearchFilterNodes',
            $this->getSettingsXpath()->query('//module/function/inputParameters/list[@id="search"]/listfield')
        );

        // output parameters
        $this->setProperty(
            'returnCodeNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="returnCode"]')->item(0)
        );
        $this->setProperty(
            'errorMessageNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="errorMessage"]')->item(0)
        );
        $this->setProperty(
            'docIdNode',
            $this->getSettingsXpath()->query('//module/function/outputParameters/field[@id="docid"]')->item(0)
        );
    }

    protected function clipPdfExecute()
    {
        if ($this->useSubtableForClipping) {
            $this->documentRevision = $this->cloneRevisionForClipping();
        }

//        $pdfCreator = new DialogPDFCreator(
//            $this->step->getWorkflowId(), $this->step->isSimulation(),
//            $this->getProperty('dialogName')
//        );
//        $inputPath = '/temp/jobarchive_clippdf_' . md5(uniqid(time())) . '.pdf';
//        $fileToClip = File::formatPath(Utility::getFullOutputPath() . $inputPath);

        $fileToClip = 'C:\\test.txt';

        // Utility::log('file', 'support', new \LogInfo(__METHOD__, 'ERROR', var_export($fileToClip, true)));

        // $pdfCreator->create($fileToClip);

        $file = new File_Filesystem($fileToClip);

        $this->createEventForJobArchiveProtocol(JobArchive_Protocol::UPDATE);
        $this->documentRevisionManager->clipFile($this->documentRevision, $file);

        if ($this->hasDeleteDate()) {
            $this->documentRevisionManager->updateDeleteDateForRevisionTree(
                $this->documentRevision->getId(),
                $this->getDeleteDate()
            );
        }

        $this->storeOutputParameter('returnCode', '');
        $this->storeOutputParameter('errorMessage', '');

        if ($this->documentRevision instanceof JobArchive_DocumentRevision) {
            $this->storeOutputParameter('docid', $this->documentRevision->getId());
        }
    }


    /********************************************************************************************************
     *
     * checkDocuments
     *
     * ******************************************************************************************************
     */

    protected function checkDocuments()
    {
        // Implementation in .NET
        // Nothing to do here
    }

    protected function exportPdf()
    {
        // Implementation in .NET
        // Nothing to do here
    }

    private function encryptIndexFieldValues($indexFieldValues)
    {
        $indexFieldManager = new JobArchive_IndexFieldManager($this->archive, $indexFieldValues);
        $indexFieldManager->encryptIndexFields();

        return $indexFieldManager->getIndexFields();
    }

    /**
     * Returns an array containing index fields for a given
     * JobArchive archive.
     *
     * @param string $parameterID - id of the html element containing the selected archive
     * @param string $elementID - id of the html element that get's the index field array
     *
     * @return array - list of index fields
     */
    protected function getContentOfUDParameter($parameterID, $elementID)
    {
        $returnFields = [];

        $userDefinedParameterName = 'udf_' . $parameterID;
        $archiveTableName = $this->activityDialogData[$userDefinedParameterName];

        if (!empty($archiveTableName)) {
            try {
                $archive = JobArchive_ArchiveFactory::getInstance($archiveTableName);
                $returnFields = $archive->getTable()->getFieldList();

                // exportPdf function is implemented in .net and does not support keyword fields
                if ($this->activityDialogData["function_id"] == static::FUNCTION_EXPORT_PDF) {
                    $returnFields = array_filter($returnFields, function($field) {
                        return $field["fieldType"] != "keyword";
                    });
                }
            } catch (JobRouterException $e) {
                MessagesManager::addMessage($e->getMessage(), MessagesManager::MSG_ERROR);
            }
        }

        if (($elementID == 'search' || $elementID == 'indexOutput') && count($returnFields) > 0) {
            array_unshift(
                $returnFields,
                [
                    'name' => CONST_JOBARCHIVEID . ' (Int)',
                    'value' => 'baserevision_id',
                ]
            );
        }

        if (count($returnFields) > 0) {
            array_unshift(
                $returnFields,
                [
                    'name' => '-',
                    'value' => '',
                ]
            );
        }

        return $returnFields;
    }

    /**
     * Returns an html listbox containing the list of
     * JobArchive archives.
     *
     * @param $settingID
     * @param string $settingName
     * @param string $settingDesc
     *
     * @return string - html listbox
     * @internal param string $id - id of the html element that gets this list
     *
     */
    protected function getContentOfUDSetting($settingID, $settingName = '', $settingDesc = '')
    {
        $content = '';
        switch ($settingID) {
            case 'jdSourceArchive':
                // exportPdf function is implemented in .net, so keyword fields as filter are not supported
                if ($this->activityDialogData["function_id"] == static::FUNCTION_EXPORT_PDF) {
                    $onChange = 'refreshUDParameters(\'jdSourceArchive\', false);';
                } else {
                    $onChange = 'refreshUDParameters(\'jdSourceArchive\');';
                }

                $content .= '
                <div class="jr-form-row">
                    <label class="jr-form-label">' . CONST_ACT_JD_SELECT_ARCHIVE . ': </label>
                    <span class="jr-form-control-wrapper">'
                    . WidgetsHelper::listbox(
                        'udf_' . $settingID,
                        $onChange,
                        JobArchive_ArchiveFactory::getArchiveList(),
                        $this->activityDialogData['udf_' . $settingID]
                    )
                    . '</span>
                </div>';
                break;
            case 'jdTargetArchive':
                $content .= '
                <div class="jr-form-row">
                    <label class="jr-form-label">' . CONST_ACT_JD_SELECT_DEST_ARCHIVE . ': </label>
                    <span class="jr-form-control-wrapper">'
                    . WidgetsHelper::listbox(
                        'udf_' . $settingID,
                        'refreshUDParameters(\'jdTargetArchive\');',
                        JobArchive_ArchiveFactory::getArchiveList(),
                        $this->activityDialogData['udf_' . $settingID]
                    )
                    . '</span>
                </div>';
                break;
            case 'dialogName':
                $content .= '
                <div class="jr-form-row">
                    <label class="jr-form-label">' . CONST_ACT_JD_SELECT_DIALOG . ': </label>
                    <span class="jr-form-control-wrapper">'
                    . WidgetsHelper::listbox(
                        'udf_' . $settingID,
                        '',
                        ProcessManager::getDialogs($this->processName, $this->version, true),
                        $this->activityDialogData['udf_' . $settingID]
                    ) . '</span>
                </div>';
                break;
            case 'fixSubtable':
                $content .= '
                <div class="jr-form-row">
                    <label class="jr-form-label">' . CONST_ACT_JD_SUBTABLE . ': </label>
                    <span class="jr-form-control-wrapper">'
                    . WidgetsHelper::listbox(
                        'udf_' . $settingID,
                        'resetSubtables();',
                        ProcessManager::getSubtablesAsArray($this->processName, $this->version, true),
                        $this->activityDialogData['udf_' . $settingID]
                    ) . '</span>
                </div>';
                break;
            default:
                $content .= '
                <div class="jr-form-row dat0">
                    <label class="jr-form-label"></label>
                    <span class="jr-form-control-wrapper"><input type=\'textbox\' id=\'udf_' . $settingID . '\' name=\'udf_' . $settingID
                    . '\'></span>
                </div>';
                break;
        }

        return $content;
    }

    /**
     * get Userdefined-Scripts
     *
     * @return string
     */
    protected function getUDScripts()
    {
        return '
        <script type=\'text/javascript\'>

            function refreshUDParameters(udList, includeKeywordFields)
            {
                var action = "refreshJobArchiveIndexFieldListsWithoutKeywordFields";
                if(arguments.length < 2 || includeKeywordFields) {
                    action =  "refreshJobArchiveIndexFieldLists";
                }
            
                var params = {action: action, selectedFileCabinet: $(\'id_udf_\' + udList).value};

                new Ajax.Request(\'index.php?cmd=Ajax_JobArchive\', {
                    method: \'get\',
                    parameters: params,
                    onComplete: function() {},
                    onLoading: function() {},
                    onSuccess: function(jsonResponse) {
                        var listEntries =  jsonResponse.responseText.evalJSON(true);
                        jobarchiveRefreshAllTableFieldLists(udList, listEntries);
                    }
                });
            }

            function jobarchiveRefreshAllTableFieldLists(udList, listEntriesReference) {

                $$(\'.\' + udList).each(function(list) {

                    var listEntries = jobarchiveCloneArray(listEntriesReference);

                    if(jobarchiveDocIdNeededForList(list)) {
                        var firstElement = listEntries.shift();
                        listEntries.unshift({value : "baserevision_id", name : "' . CONST_JOBARCHIVEID
            . '"});
                        listEntries.unshift(firstElement);
                    }

                    jobarchiveRefreshListItems(list, listEntries);
                });
            }

            function jobarchiveRefreshListItems(list, listEntries) {
                removeAllListEntries(list);
                listEntries.each(function(option) {
                    newOption = new Element(\'option\', {\'value\': option.value}).update(option.name);
                    $(list).insert(newOption);
                });
            }

            function removeAllListEntries(list) {
                $(list).update();
            }

            function jobarchiveDocIdNeededForList(list) {
                return (list.id.indexOf(\'list_search\') == 0);
            }

            function jobarchiveCloneArray(arrayToClone) {
                return arrayToClone.slice(0);
            }

        </script>';
    }
}
