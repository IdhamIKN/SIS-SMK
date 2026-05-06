<?php

if (! function_exists('sekolah_data')) {
    /**
     * Get sekolah data from cache
     */
    function sekolah_data()
    {
        return app('sekolah.data');
    }
}

if (! function_exists('tahun_ajaran_aktif')) {
    /**
     * Get active tahun ajaran
     */
    function tahun_ajaran_aktif()
    {
        return app('sekolah.tahun_ajaran');
    }
}

if (! function_exists('jam_shift_config')) {
    /**
     * Get jam shift configuration
     */
    function jam_shift_config()
    {
        return app('sekolah.jam_shift');
    }
}

if (! function_exists('threshold_alfa_config')) {
    /**
     * Get threshold alfa configuration
     */
    function threshold_alfa_config()
    {
        return app('sekolah.threshold_alfa');
    }
}

if (! function_exists('azures_snackbar')) {
    /**
     * Generate Azures snackbar JavaScript
     */
    function azures_snackbar($message, $bg = 'bg-blue-dark', $duration = 3000)
    {
        return "snackbar('".addslashes($message)."', '".$bg."', ".$duration.');';
    }
}

if (! function_exists('azures_toast_success')) {
    /**
     * Success toast
     */
    function azures_toast_success($message, $duration = 3000)
    {
        return azures_snackbar($message, 'bg-green-dark', $duration);
    }
}

if (! function_exists('azures_toast_error')) {
    /**
     * Error toast
     */
    function azures_toast_error($message, $duration = 4000)
    {
        return azures_snackbar($message, 'bg-red-dark', $duration);
    }
}

if (! function_exists('azures_toast_warning')) {
    /**
     * Warning toast
     */
    function azures_toast_warning($message, $duration = 3500)
    {
        return azures_snackbar($message, 'bg-orange-dark', $duration);
    }
}
