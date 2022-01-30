<?php

function flash( $type, $message )
{
    $bag = session( $type, new \Illuminate\Support\MessageBag );

    $key = sprintf( "%s", 'key_' . md5( microtime( true ) ) );

    session()->flash( $type, $bag->add( $key, $message ) );
}

function flash_error( $message )
{
    flash( 'error', $message );
}

function flash_info( $message )
{
    flash( 'info', $message );
}

function flash_success( $message )
{
    flash( 'success', $message );
}

function flash_warning( $message )
{
    flash( 'warning', $message );
}

function option( $name, $label, $value, $default = '' )
{
    $selected = ( old( $name, $default ) == $value ? 'selected' : '' );

    return sprintf( '<option value="%s" %s>%s</option>', $value, $selected, $label );
}

function checkbox( $name, $label, $value, $default = '', $isDisabled = false, $isInline = false )
{
    $disabled = $isDisabled ? 'disabled' : '';
    $checked  = old( $name, $default ) == $value ? 'checked' : '';

    return sprintf(
        '<div class="checkbox%s %s"><label><input type="checkbox" name="%s" value="%s" %s %s> %s</label></div>',
        ($isInline ? '-inline' : ''), $disabled, $name, $value, $checked, $disabled, $label
    );
}

function radio( $name, $label, $value, $default = '', $isDisabled = false )
{
    $disabled = $isDisabled ? 'disabled' : '';
    $checked  = old( $name, $default ) == $value ? 'checked' : '';

    return sprintf(
        '<div class="radio %s"><label><input type="radio" name="%s" %s %s> %s</label></div>',
        $disabled, $name, $checked, $disabled, $label
    );
}

function special_attributes()
{
    return 'autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"';
}

/**
 * @return \Carbon\Carbon
 */
function tz()
{
    return config( 'app.timezone', 'America/Sao_Paulo' );
}

/**
 * @return \Carbon\Carbon
 */
function now()
{
    return \Carbon\Carbon::now( tz() );
}

/**
 * @return \Carbon\Carbon
 */
function today()
{
    return now()->setTime( 0, 0, 0 );
}

function input_changed()
{
    $nochanges = session( 'no-changes', false );

    return !$nochanges && session()->hasOldInput();
}

function redirect_current( $status = 302, $headers = [ ] )
{
    return app( 'redirect' )->to( session( 'url.current', route( 'home' ) ), $status, $headers );
}

function redirect_previous( $status = 302, $headers = [ ] )
{
    return app( 'redirect' )->to( session( 'url.previous', route( 'home' ) ), $status, $headers );
}
