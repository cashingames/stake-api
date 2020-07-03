<?php

interface Voucher {

    /**
     * Factory method to cerate a new voucher
     */
    function generate();

    /**
     * Inspect whether valid or not
     */
    // function validate(string $code);

    /**
     * Applies the voucher
     */
    function consume($code);

    /**
     * Invalidates for future use
     */
    function consumeAll($code);
}
