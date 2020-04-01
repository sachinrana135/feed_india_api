<?php
/**
 * Created by PhpStorm.
 * User: Velocity-1601
 * Date: 9/23/2017
 * Time: 10:40 PM
 */

function utf8_substr($string, $offset, $length = null)
{
    // generates E_NOTICE
    // for PHP4 objects, but not PHP5 objects
    $string = (string)$string;
    $offset = (int)$offset;

    if (!is_null($length)) {
        $length = (int)$length;
    }

    // handle trivial cases
    if ($length === 0) {
        return '';
    }

    if ($offset < 0 && $length < 0 && $length < $offset) {
        return '';
    }

    // normalise negative offsets (we could use a tail
    // anchored pattern, but they are horribly slow!)
    if ($offset < 0) {
        $strlen = strlen(utf8_decode($string));
        $offset = $strlen + $offset;

        if ($offset < 0) {
            $offset = 0;
        }
    }

    $Op = '';
    $Lp = '';

    // establish a pattern for offset, a
    // non-captured group equal in length to offset
    if ($offset > 0) {
        $Ox = (int)($offset / 65535);
        $Oy = $offset % 65535;

        if ($Ox) {
            $Op = '(?:.{65535}){' . $Ox . '}';
        }

        $Op = '^(?:' . $Op . '.{' . $Oy . '})';
    } else {
        $Op = '^';
    }

    // establish a pattern for length
    if (is_null($length)) {
        $Lp = '(.*)$';
    } else {
        if (!isset($strlen)) {
            $strlen = strlen(utf8_decode($string));
        }

        // another trivial case
        if ($offset > $strlen) {
            return '';
        }

        if ($length > 0) {
            $length = min($strlen - $offset, $length);

            $Lx = (int)($length / 65535);
            $Ly = $length % 65535;

            // negative length requires a captured group
            // of length characters
            if ($Lx) {
                $Lp = '(?:.{65535}){' . $Lx . '}';
            }

            $Lp = '(' . $Lp . '.{' . $Ly . '})';
        } elseif ($length < 0) {
            if ($length < ($offset - $strlen)) {
                return '';
            }

            $Lx = (int)((-$length) / 65535);
            $Ly = (-$length) % 65535;

            // negative length requires ... capture everything
            // except a group of  -length characters
            // anchored at the tail-end of the string
            if ($Lx) {
                $Lp = '(?:.{65535}){' . $Lx . '}';
            }

            $Lp = '(.*)(?:' . $Lp . '.{' . $Ly . '})$';
        }
    }

    if (!preg_match('#' . $Op . $Lp . '#us', $string, $match)) {
        return '';
    }

    return $match[1];

}

function utf8_strpos($string, $needle, $offset = NULL)
{
    if (is_null($offset)) {
        $data = explode($needle, $string, 2);

        if (count($data) > 1) {
            return utf8_strlen($data[0]);
        }

        return false;
    } else {
        if (!is_int($offset)) {
            trigger_error('utf8_strpos: Offset must be an integer', E_USER_ERROR);

            return false;
        }

        $string = utf8_substr($string, $offset);

        if (false !== ($position = utf8_strpos($string, $needle))) {
            return $position + $offset;
        }

        return false;
    }
}

function utf8_strrpos($string, $needle, $offset = NULL)
{
    if (is_null($offset)) {
        $data = explode($needle, $string);

        if (count($data) > 1) {
            array_pop($data);

            $string = join($needle, $data);

            return utf8_strlen($string);
        }

        return false;
    } else {
        if (!is_int($offset)) {
            trigger_error('utf8_strrpos expects parameter 3 to be long', E_USER_WARNING);

            return false;
        }

        $string = utf8_substr($string, $offset);

        if (false !== ($position = utf8_strrpos($string, $needle))) {
            return $position + $offset;
        }

        return false;
    }
}

function utf8_strlen($string)
{
    return strlen(utf8_decode($string));
}

