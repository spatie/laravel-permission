<?php

function isNotLumen() : bool
{
    return ! preg_match('/lumen/i', app()->version());
}
