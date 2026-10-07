<?php

namespace App\Http\Requests;

class JurnalMengajarUpdateRequest extends JurnalMengajarStoreRequest
{
    // Bukti opsional saat update — override rule agar tidak required
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['bukti_kamera'] = ['nullable', 'image', 'mimes:' . self::MIMES_GAMBAR, 'max:10240'];
        $rules['bukti_file']   = ['nullable', 'file',  'mimes:' . self::MIMES_FILE,   'max:10240'];

        return $rules;
    }
}
