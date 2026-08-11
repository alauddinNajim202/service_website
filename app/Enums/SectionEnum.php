<?php

namespace App\Enums;

enum SectionEnum: string
{
    const BG = 'bg_image';

    case EXAMPLE = 'example';
    case EXAMPLES = 'examples';

    case INTRO = 'intro';

    case ABOUT = 'about';
    case SAFE_SPACE = 'safe_space';

    //common sections
    case FOOTER = 'footer';
    case HEADER = 'header';



    
}
