# ************************************
# Apache vhost file for <?= $_project_file_path ?> [project=<?= $_project_id ?>]
# Managed by dashbrew
# ************************************

<VirtualHost *:<?= $vhost['port'] ?>>
    ## Vhost document root
    DocumentRoot "<?= $vhost['docroot'] ?>"

    ## Vhost server name
    ServerName <?= $vhost['servername'] ?>
    <?php if(!empty($vhost['serveraliases'])): foreach((array) $vhost['serveraliases'] as $serveralias): ?> 
    ServerAlias <?= $serveralias ?>
    <?php endforeach; endif; ?>

    <?php if(!empty($vhost['fallbackresource'])): ?> 
    FallbackResource <?= $vhost['fallbackresource'] ?>
    <?php endif; ?> 

    ## Directories, there should at least be a declaration for <?= $vhost['docroot'] ?>
    <?php foreach($vhost['directories'] as $dir): ?> 
    <<?= $dir['provider'] ?> "<?= $dir['path'] ?>">
        <?php if(!empty($dir['headers'])): foreach((array) $dir['headers'] as $header): ?> 
        Header <?= $header ?>
        <?php endforeach; endif; ?>
        <?php if(!empty($dir['options'])): ?> 
        Options <?= implode(' ', (array) $dir['options']) ?>
        <?php endif; ?>
        <?php if($dir['provider'] == 'Directory'): ?>
            <?php if(!empty($dir['index_options'])): ?> 
        IndexOptions <?= implode(' ', (array) $dir['index_options']) ?>
            <?php endif; ?>
            <?php if(!empty($dir['index_order_default'])): ?> 
        IndexOrderDefault <?= implode(' ', (array) $dir['index_order_default']) ?>
            <?php endif; ?>
            <?php if(!empty($dir['allow_override'])): ?> 
        AllowOverride <?= implode(' ', (array) $dir['allow_override']) ?>
            <?php else: ?> 
        AllowOverride None
            <?php endif; ?>
        <?php endif; ?>
        <?php if(!empty($dir['require'])): ?> 
        Require <?= implode(' ', (array) $dir['require']) ?>
        <?php else: ?> 
        Require all granted
        <?php endif; ?>
        <?php if(!empty($dir['satisfy'])): ?> 
        Satisfy <?= $vhost['satisfy'] ?>
        <?php endif; ?>
        <?php if(!empty($dir['addhandlers'])): foreach((array) $dir['addhandlers'] as $addhandler): ?> 
        AddHandler <?= $addhandler['handler'] ?> <?= implode(' ', (array) $addhandler['extensions']) ?>
        <?php endforeach; endif; ?>
        <?php if(!empty($dir['sethandler'])): ?> 
        SetHandler <?= $dir['sethandler'] ?>
        <?php endif; ?>
        <?php if(!empty($dir['directoryindex'])): ?> 
        DirectoryIndex <?= $vhost['directoryindex'] ?>
        <?php endif; ?>
        <?php if(!empty($dir['error_documents'])): foreach((array) $dir['error_documents'] as $error_document): ?>
        <?php if(empty($error_document['error_code']) || empty($error_document['document'])) continue; ?> 
        ErrorDocument <?= $error_document['error_code'] ?> <?= $error_document['document'] ?>
        <?php endforeach; endif; ?>
        <?php if(!empty($dir['fallbackresource'])): ?> 
        FallbackResource <?= $dir['fallbackresource'] ?>
        <?php endif; ?>
        <?php if(!empty($dir['ssl_options'])): ?> 
        SSLOptions <?= implode(' ', (array) $dir['ssl_options']) ?>
        <?php endif; ?>

        <?php if(!empty($dir['custom_fragment'])): ?> 
        # Custom fragment
        <?= $dir['custom_fragment'] ?>
        <?php endif; ?> 
    </<?= $dir['provider'] ?>>
    <?php endforeach; ?>

    <?php if(!empty($vhost['reverseproxy']['path']) && !empty($vhost['reverseproxy']['url'])): ?> 
    ProxyRequests Off
    ProxyPassReverse <?= $vhost['reverseproxy']['path'] ?> <?= $vhost['reverseproxy']['url'] ?> 
    ProxyPass <?= $vhost['reverseproxy']['path'] ?> <?= $vhost['reverseproxy']['url'] ?> 
    ProxyPreserveHost Off
    <?php endif; ?>

    <?php if(!empty($vhost['includes'])): foreach((array) $vhost['includes'] as $include): ?> 
    ## Load additional static includes
    IncludeOptional "<?= $include ?>"
    <?php endforeach; endif; ?>

    <?php if(!empty($vhost['serveradmin'])): ?> 
    ServerAdmin <?= $vhost['serveradmin'] ?>
    <?php endif; ?> 
    ServerSignature On

    ## SetEnv/SetEnvIf for environment variables
    <?php if(!empty($vhost['setenv'])): foreach((array) $vhost['setenv'] as $envvar): ?> 
    SetEnv <?= $envvar ?>
    <?php endforeach; endif; ?>
    <?php if(!empty($vhost['setenvif'])): foreach((array) $vhost['setenvif'] as $envifvar): ?> 
    SetEnvIf <?= $envifvar ?>
    <?php endforeach; endif; ?> 

    ## Logging
    <?php if(!empty($vhost['error_log'])): ?> 
    ErrorLog "<?= $vhost['error_log'] ?>"
    <?php endif; ?>
    <?php if(!empty($vhost['log_level'])): ?> 
    LogLevel <?= $vhost['log_level'] ?>
    <?php endif; ?>
    <?php if(!empty($vhost['access_log'])): ?> 
    CustomLog "<?= $vhost['access_log'] ?>" combined
    <?php endif; ?>

    <?php if(!empty($vhost['error_documents'])): foreach((array) $vhost['error_documents'] as $error_document): ?>
    <?php if(empty($error_document['error_code']) || empty($error_document['document'])) continue; ?> 
    ErrorDocument <?= $error_document['error_code'] ?> <?= $error_document['document'] ?>
    <?php endforeach; endif; ?>

    <?php if($vhost['port'] == '443'): ?> 
    ## SSL directives
    SSLEngine on
    SSLCertificateFile      "<?= $vhost['ssl_cert'] ?>"
    SSLCertificateKeyFile   "<?= $vhost['ssl_key'] ?>"
        <?php if(!empty($vhost['ssl_chain'])): ?> 
    SSLCertificateChainFile "<?= $vhost['ssl_chain'] ?>"
        <?php endif; ?>
        <?php if(!empty($vhost['ssl_certs_dir'])): ?> 
    SSLCACertificatePath "<?= $vhost['ssl_certs_dir'] ?>"
        <?php endif; ?>
        <?php if(!empty($vhost['ssl_ca'])): ?> 
    SSLCACertificateFile "<?= $vhost['ssl_ca'] ?>"
        <?php endif; ?>
        <?php if(!empty($vhost['ssl_crl_path'])): ?> 
    SSLCARevocationPath "<?= $vhost['ssl_crl_path'] ?>"
        <?php endif; ?>
        <?php if(!empty($vhost['ssl_crl'])): ?> 
    SSLCARevocationFile "<?= $vhost['ssl_crl'] ?>"
        <?php endif; ?>
        <?php if(!empty($vhost['ssl_proxyengine'])): ?> 
    SSLProxyEngine On
        <?php endif; ?>
        <?php if(!empty($vhost['ssl_protocol'])): ?> 
    SSLProtocol <?= $vhost['ssl_protocol'] ?>
        <?php endif; ?>
        <?php if(!empty($vhost['ssl_cipher'])): ?> 
    SSLCipherSuite <?= $vhost['ssl_cipher'] ?>
        <?php endif; ?>
        <?php if(!empty($vhost['ssl_honorcipherorder'])): ?> 
    SSLHonorCipherOrder <?= $vhost['ssl_honorcipherorder'] ?>
        <?php endif; ?>
        <?php if(!empty($vhost['ssl_verify_client'])): ?> 
    SSLVerifyClient <?= $vhost['ssl_verify_client'] ?>
        <?php endif; ?>
        <?php if(!empty($vhost['ssl_verify_depth'])): ?> 
    SSLVerifyDepth <?= $vhost['ssl_verify_depth'] ?>
        <?php endif; ?>
        <?php if(!empty($dir['ssl_options'])): ?> 
    SSLOptions <?= implode(' ', (array) $dir['ssl_options']) ?>
        <?php endif; ?>
    <?php endif; ?> 
</VirtualHost>

# vim: syntax=apache ts=4 sw=4 sts=4 sr noet
