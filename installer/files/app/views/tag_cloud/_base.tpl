<link href="{tc_stylesheet}" rel="stylesheet"/>
<div id="tag_cloud">
<ul class="cloud">
{loop tc_tags}
<li class="{tc_tag-class}"><a {?tc_tag-link}href="{tc_tag-link}"{end}>{tc_tag-tag}</a></li>
{end}
</ul>
</div>