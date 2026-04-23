<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Core\Gui\Support
{
    /**
     * DataTypes short summary.
     *
     * DataTypes description.
     *
     * @version 1.0
     * @author MikeCorner
     */
    abstract class FieldTypeList extends \Enum
    {
        const INPUT     = 'Input';
        const CHECKBOX  = 'Checkbox';
        const RADIO     = 'Radio';
        const FILE      = 'File';
        const IMAGE     = 'Image';
        const BUTTON    = 'Button';
        const TEXT      = 'Textarea';
        const LABEL     = 'Label';
        const PASSWORD  = 'Password';
        const FORM      = 'Form';
        const HIDDEN    = 'Hidden';
        const NULL      = null;
    }
}