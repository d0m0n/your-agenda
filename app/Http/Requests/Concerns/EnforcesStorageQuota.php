<?php

namespace App\Http\Requests\Concerns;

use App\Services\StorageUsageService;
use Illuminate\Contracts\Validation\Validator;

/**
 * Rejects new uploads once the acting user's organization has reached its
 * storage quota. Applied in withValidator() so it runs after the normal
 * file/size rules have already passed, using each file's real upload size.
 */
trait EnforcesStorageQuota
{
    /**
     * @param  list<string>  $fileFields
     */
    protected function enforceStorageQuota(Validator $validator, array $fileFields): void
    {
        $validator->after(function (Validator $validator) use ($fileFields) {
            $additionalBytes = 0;

            foreach ($fileFields as $field) {
                $file = $this->file($field);
                if ($file) {
                    $additionalBytes += $file->getSize();
                }
            }

            if ($additionalBytes === 0) {
                return;
            }

            if (app(StorageUsageService::class)->wouldExceedQuota($this->user(), $additionalBytes)) {
                $validator->errors()->add(
                    $fileFields[0],
                    'データ容量の上限に達しているため、アップロードできません。基本設定の使用量をご確認ください。'
                );
            }
        });
    }
}
