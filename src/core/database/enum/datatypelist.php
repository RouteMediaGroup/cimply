<?php
namespace Cimply\Core\Database\Enum
{
    /**
     * DataTypes short summary.
     *
     * DataTypes description.
     *
     * @version 1.0
     * @author MikeCorner
     */
    abstract class DataTypeList extends \Enum
    {
        const DECIMAL = 0;
        const TINYINT = 1;
        const SMALLINT = 2;
        const INT = 3;
        const FLOAT = 4;
        const DOUBLE = 5;
        const TIMESTAMP = 7;
        const BIGINT = 8;
        const MEDIUMINT = 9;
        const DATE = 10;
        const TIME = 11;
        const DATETIME = 12;
        const YEAR = 13;
        const BIT = 16;
        const DECIMAL = 246;
        const ENUM = 247;
        const SET = 248;
        const TINYBLOB = 249;
        const SMALLBLOB = 250;
        const BIGBLOB = 251;
        const TEXT = 252;
        const VARCHAR = 253;
        const CHAR = 254;
        const GEOMETRY = 255;
        const ENUMERIC = 256;
        const PRIMARY = 49667;
        const PRIMARY_UNIQUE = 53255;
        const UNIQUE = 16388;
        const FOREIGN = 53251;
        const INDEX = 53259;
        const INCREMENT = 49675;
        const NULL = 6;
    }
}