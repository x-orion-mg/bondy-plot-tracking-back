<?php

namespace App\Utils;

/**
 * Class PathReportingName
 * Class that contains the constant names of the reporting directories
 */
class PathName
{
    const PUBLIC = '/public/';
    const UPLOAD_LOGO_PARTNER = 'uploads/partner/logo/';
    const UPLOAD_BANNER_PROJECT_PARENT = 'uploads/project-parent/banners/';
    const UPLOAD_BANNER_PROJECT_CHILD = 'uploads/project-child/banners/';
    const UPLOAD_BANNER_PARCELS = 'uploads/parcel/banners/';
    const UPLOAD_BANNER_TREE_SPECIES = 'uploads/tree-species/banners/';
    const UPLOAD_BANNER_STORY_TELLING = 'uploads/story-telling/banners/';
    const UPLOAD_GALLERIES = 'uploads/galleries/';
    const UPLOAD_THUMBNAIL_GALLERIES = 'uploads/galleries/thumbnails/';
    const UPLOAD_IMG_DRONE_PARCEL = 'uploads/parcel/image-drone/images';
    const UPLOAD_ICON_TREE_SPECIES = 'uploads/tree-species/icon/';
    const UPLOAD_ICON_USAGE_TYPE = 'uploads/usage-type/icon/';
    const UPLOAD_CSV_CUSTOMER_IMPORT = 'uploads/customer-import/csv/';
    const UPLOAD_CSV_CUSTOMER_REPORT_IMPORT = 'uploads/customer-import/report/';
    const EXPORT_EXCEL_CUSTOMERS_FINALS = 'uploads/export/excel/customers-finals/';
    const EXPORT_QRCODE_CUSTOMERS_FINALS = 'uploads/export/qrcodes/customers-finals/';

    const PROFILE_SUPER_ADMIN = 'SUPER_ADMIN';
    const PROFILE_ADMIN = 'ADMIN';
}
