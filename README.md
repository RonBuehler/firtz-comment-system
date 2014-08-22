#firtz-comment-system
*firtz comment system*  
*Version 0.1b*


##Über

Dieses Kommentarsystem für den [firtz podcast publisher](http://firtz.org/) entstand aus der Idee heraus, das darin eingebundene Disqus-Kommentarsystem durch eine Eigenentwicklung zu ersetzen um von diesem Dienst unabhängig zu sein.


##Voraussetzungen
* Eine laufende [Firtz-Installation](https://github.com/eazyliving/firtz/)


##Installation
Folgende Schritte müssen für die Installation durchgeführt werden:

* Das [Firtz Comment System](https://github.com/RonBuehler/firtz-comment-system) herunterladen und in den Ordner der Firtz-Installation entpacken.

* Änderungen an den folgenden Dateien durchführen:

      Einfügen in dict/de.php
    ```
    'dict_comm_required'=>'Pflichtfeld',
    'dict_comm_name'=>'Dein Name',
    'dict_comm_email'=>'Email',
    'dict_comm_website'=>'Website',
    'dict_comm_message'=>'Kommentar',
    'dict_comm_submit'=>'Abschicken'
    ```
    
      Einfügen in dict/en.php
    ```
    'dict_comm_required'=>'Required Field',
    'dict_comm_name'=>'Your name',
    'dict_comm_email'=>'Email',
    'dict_comm_website'=>'Website',
    'dict_comm_message'=>'Comment',
    'dict_comm_submit'=>'Submit'
    ```
    
      Einfügen  in feed.cfg
    ```
    #: Dieser Punkt entscheided darüber, ob Disqus oder das neue Kommentarsystem genutzt wird
    commentsystem:
    #: disqus
    firtz
    ```
    
      Ändern an index.php
    ```
    ***Vor die Zeile $firtz = new firtz($main); einfügen:***
    
    $comments = new comments($main);
    $main->set('comments',$comments);
    ```
    
      Ändern in templates/site.html
    ```
    <check if="{{@feedattr.disqus}}">
	<include href="disqus.html"/>
    </check>

    *** ersetzen durch ***
    
    <check if="{{@feedattr.commentsystem == 'disqus'}}">
      	<check if="{{@feedattr.disqus}}">
	      	<include href="disqus.html"/>
      	</check>
    </check>
    <check if="{{@feedattr.commentsystem == 'firtz'}}">
      	<include href="comments.html"/>
    </check>
    ```

    ```
    <check if="{{@feedattr.disqus}}">
        <include href="disqus_multiple.js"/>
    </check>
    
    *** ersetzen durch ***
    
    <check if="{{@feedattr.commentsystem == 'disqus'}}">
      	<check if="{{@feedattr.disqus}}">
      	      	<include href="disqus_multiple.js"/>
      	</check>
    </check>
    <check if="{{@feedattr.commentsystem == 'firtz'}}">
      	<include href="comments.js"/>
    </check>    
    ```
    
* Der Ordner comments/admin benötigt einen Verzeichnisschutz.
Im Zweifel bitte an den Provider wenden.

* Der Ordner comments/files benötigt Schreibrechte, damit dort die Kommentardateien abgelegt werden können.  
Im Zweifel auch hier an den Provider wenden.


##Administration
Prinzipiell werden alle neuen Kommentare erst einmal zur Moderation eingetragen. Sie sind also erstmal nicht sichtbar.
Um diese Kommentare freizuschalten, müsst ihr euch in den Adminbereich einloggen (Siehe Hinweis bei Installation zum Thema Verzeichnisschutz).
Hierzu geht ihr auf http://urlderwebseite.xxx/comments/admin und loggt euch ein.
Nun habt ihr die Möglichkeit, die Kommentare, sofern vorhanden, auszublenden, einzublenden oder zu löschen.


##Importer


##Kontakt

[E-Mail](mailto:ronbuehler@live.de) || [Twitter](https://twitter.com/ronbuehler) || [app.net](https://alpha.app.net/ronbuehler)

