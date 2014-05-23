/**
 * Materials table
 * @param table DOM table element
 */
function SamsonCMSTable ( table )
{		
	/* Russian messages */
	var ru = {
		publish:'Опубликовать?',
		unpublish:'Снять с публикации?',
		remove:'Удалить материал?',
		copy:'Скопировать материал?'
	};
	
	/* English messages */
	var en = {
		publish:'Опубликовать?',
		unpublish:'Снять с публикации?',
		remove:'Удалить материал?',
		copy:'Скопировать материал?'
	};
	
	// Pointer to current locale
	var i18n = ru;
	
	/** Event: Publish/unpublish material */
	function publish( obj )
	{		
		// Спросим подтверждение 
		if( confirm( ( obj.a('checked') ) ? i18n.publish : i18n.unpublish  ))
		{			
			// Perform ajax request and update JS on success
			s.ajax( s( 'a.publish_href', obj.parent()).a('href'), init );			
		}
	};
	
	/** Event: Remove material */
	function remove( obj ){	if( confirm( i18n.remove ) ) s.ajax( obj.a('href'), init );	};
	
	/** Event: Copy material */
	function copy( obj ){ if( confirm( i18n.copy ) ) s.ajax( obj.a('href'), init ); };
	
	/**
	 * Обновить таблицу материалов
	 * 
	 * @param data Содержание таблицы для обновления
	 */
	function init( serverResponse )
	{		
		// If we have responce from server
		if( serverResponse ) try
		{
			// Parse JSON responce
			serverResponse = JSON.parse( serverResponse );
					
			// If we have table html - update it
			if( serverResponse.table_html ) table.html( serverResponse.table_html );			
			if( serverResponse.pager_html ) s('.table-pager').html( serverResponse.pager );
		}		
		catch(e){ s.trace('Ошибка обработки ответа полученного от сервера, повторите попытку отправки данных'); };		
		
		// If we have succesful event responce or no responce at all(first init)
		if( !serverResponse || (serverResponse && serverResponse.status) )
		{					
			// Add fixed header to materials table
			s('.material-table').fixedHeader();
			
			// Bind publish event
			s( 'input#published' ).click( publish, true, true );
			
			// Bind remove event
			s( 'a.delete' ).click( remove, true, true );				
		}
	};	
	
	function material_search( search )
	{
		// Safely get object
		search = s(search);
		
		var cmsnav = 0;//s('#cmsnav_id').val(); 
		var page = 1;
		
		// Ajax request handle
		var request;
		var timeout;
		
		// Key up handler
		search.keyup(function(obj)
		{		
			if( request == undefined )
			{			
				// Reset timeout on key press
				if ( timeout != undefined ) clearTimeout( timeout );
				
				// Set delayed function
				timeout = window.setTimeout(function()
				{			
					// Get search input
					var keywords = obj.val();
					
					if ( keywords.length < 2 ) keywords = '';
					
					// Disable input
					search.DOMElement.enabled = false;
					
					// Perform async request to server for rendering table
					request = s.ajax( SamsonPHP.url_base('material/update/table',cmsnav,keywords,page), function(response)
					{
						// re-render table
						init(response);
						
						// Clear request variable
						request = undefined;
					});
					
				}, 1000);
			} 
		});
	}
	
	// Init table live search
	material_search( 'input#search' );	
	
	// Init table
	init();
};

/**
 * Инициализация JS для таблицы материалов
 */
s('#material').pageInit( function( _parent ) 
{		
	// Повесим обобщенный обработчик таблицы
	SamsonCMSTable( s('.material-table') );
});

s('.material-table').pageInit(function(table)
{
	table.fixedHeader();
}); 