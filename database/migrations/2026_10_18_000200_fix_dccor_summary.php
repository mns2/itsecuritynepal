<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The live WordPress DCCOR page carried the CCNA course text by mistake (copy-paste), which the import kept as the
 * course summary. Replace it with the first sentence of the page's own "Course Overview", only if the wrong text
 * is still there (never overwrite an edit made in the admin panel).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('courses')
            ->where('slug', 'cisco-dccor-350-601')
            ->where('summary', 'like', 'The Implementing and Administering Cisco Solutions (CCNA)%')
            ->update(['summary' => 'The Data Center Core Technologies course provides a comprehensive understanding of modern data center infrastructure, focusing on the core components and technologies that power data centers today.']);
    }

    public function down(): void
    {
        // Data fix only.
    }
};
