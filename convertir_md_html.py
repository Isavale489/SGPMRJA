import re

# Leer el archivo Markdown
with open('DICCIONARIO_DATOS.md', 'r', encoding='utf-8') as f:
    content = f.read()

# HTML header
html = '''<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diccionario de Datos - Sistema Atlántico</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background: #f5f7fa; padding: 40px 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 8px; }
        h1 { color: #2c3e50; font-size: 2.5em; margin-bottom: 10px; border-bottom: 4px solid #3498db; padding-bottom: 15px; }
        h2 { color: #34495e; font-size: 1.8em; margin-top: 40px; margin-bottom: 20px; border-bottom: 2px solid #95a5a6; padding-bottom: 10px; }
        h3 { color: #555; font-size: 1.3em; margin-top: 30px; margin-bottom: 15px; background: #ecf0f1; padding: 10px 15px; border-left: 4px solid #3498db; }
        p { margin: 10px 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        thead { background: #3498db; color: white; }
        thead th { padding: 12px; text-align: left; font-weight: 600; text-transform: uppercase; font-size: 0.9em; }
        tbody td { padding: 10px 12px; border: 1px solid #ddd; }
        tbody tr:nth-child(even) { background-color: #f8f9fa; }
        tbody tr:hover { background-color: #e8f4f8; }
        strong { color: #2980b9; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: 'Consolas', 'Monaco', monospace; color: #c7254e; }
        hr { border: none; border-top: 2px solid #ecf0f1; margin: 30px 0; }
        ul { margin: 15px 0 15px 30px; }
        ul li { margin: 8px 0; color: #555; }
    </style>
</head>
<body>
    <div class="container">
'''

# Convertir Markdown a HTML
lines = content.split('\n')
in_table = False
table_buffer = []

for line in lines:
    line = line.strip()
    
    # Headers
    if line.startswith('# '):
        html += f'<h1>{line[2:]}</h1>\n'
    elif line.startswith('## '):
        html += f'<h2>{line[3:]}</h2>\n'
    elif line.startswith('### '):
        html += f'<h3>{line[4:]}</h3>\n'
    # Bold text
    elif line.startswith('**') and line.endswith('**'):
        html += f'<p><strong>{line[2:-2]}</strong></p>\n'
    # Horizontal rule
   elif line == '---':
        html += '<hr>\n'
    # Table rows
    elif line.startswith('|'):
        if not in_table:
            html += '<table>\n<thead>\n'
            in_table = True
            table_is_header = True
        
        cells = [cell.strip() for cell in line.split('|')[1:-1]]
        
        # Skip separator line
        if all(c.replace('-', '').strip() == '' for c in cells):
            html += '</thead>\n<tbody>\n'
            continue
        
        if table_is_header:
            html += '<tr>' + ''.join(f'<th>{cell}</th>' for cell in cells) + '</tr>\n'
            table_is_header = False
        else:
            html += '<tr>' + ''.join(f'<td>{cell}</td>' for cell in cells) + '</tr>\n'
    # List items
    elif line.startswith('- '):
        if not html.endswith('</ul>\n'):
            html += '<ul>\n'
        html += f'<li>{line[2:]}</li>\n'
    # Paragraph
    elif line and not line.startswith('#'):
        if html.endswith('</ul>\n'):
            pass  # Don't add paragraph after list
        else:
            html += f'<p>{line}</p>\n'
    # Empty line
    else:
        if in_table:
            html += '</tbody>\n</table>\n'
            in_table = False
        if html.endswith('</ul>\n'):
            pass  # Already closed list

# Close table if still open
if in_table:
    html += '</tbody>\n</table>\n'

# HTML footer
html += '''
    </div>
</body>
</html>
'''

# Escribir archivo HTML
with open('DICCIONARIO_DATOS.html', 'w', encoding='utf-8') as f:
    f.write(html)

print("✓ Archivo HTML generado exitosamente")
