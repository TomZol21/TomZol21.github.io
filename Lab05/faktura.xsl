<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <!-- pomocniczny template rekurencyjny sumujący pozycje -->
  <xsl:template name="sum">
    <xsl:param name="nodes"/>
    <xsl:param name="pos" select="1"/>
    <xsl:param name="count" select="count($nodes)"/>
    <xsl:param name="acc" select="0"/>

    <xsl:choose>
      <xsl:when test="$pos &gt; $count">
        <xsl:value-of select="format-number($acc, '#,##0.00')"/>
      </xsl:when>
      <xsl:otherwise>
        <!-- oblicz subtotal dla bieżącej pozycji -->
        <xsl:variable name="qty" select="number($nodes[$pos]/Ilość)"/>
        <xsl:variable name="price" select="number($nodes[$pos]/CenaJednostkowa)"/>
        <xsl:variable name="subtotal" select="$qty * $price"/>
        <xsl:call-template name="sum">
          <xsl:with-param name="nodes" select="$nodes"/>
          <xsl:with-param name="pos" select="$pos + 1"/>
          <xsl:with-param name="count" select="$count"/>
          <xsl:with-param name="acc" select="$acc + $subtotal"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="/Faktura">
    <div class="invoice-data" style="font-family: Arial, sans-serif; font-size:14px;">

      <div class="header">
        <div><xsl:value-of select="Nip"/></div><br/>
        <div class="name"><xsl:value-of select="Nabywca/Imie"/><div style="margin:5px"> </div><xsl:value-of select="Nabywca/Nazwisko"/></div><br/>
        <div class="address"><xsl:value-of select="Nabywca/Ulica"/> <xsl:value-of select="Nabywca/Miasto"/></div>
      </div>

      <br/>

      <table class="items" border="0" cellpadding="4" cellspacing="0" style="border-collapse:collapse;">
        <!-- lista pozycji, wyliczamy subtotal przy każdej -->
        <xsl:for-each select="Towary/Towar">
          <tr>
            <td><xsl:value-of select="Nazwa"/></td>
            <td style="text-align:right; padding-left: 266px;"><xsl:value-of select="Ilość"/></td>
            <td style="text-align:right; padding-left: 35px;"><xsl:value-of select="format-number(number(CenaJednostkowa), '#,##0.00')"/></td>
            <td style="text-align:right; padding-left: 54px;">
              <xsl:value-of select="format-number(number(Ilość) * number(CenaJednostkowa), '#,##0.00')"/>
            </td>
          </tr>
        </xsl:for-each>
      </table>

      <br/>

      <!-- wywołujemy rekurencyjne sumowanie -->
      <div class="total">
        <xsl:call-template name="sum">
          <xsl:with-param name="nodes" select="Towary/Towar"/>
        </xsl:call-template>
      </div>

    </div>
  </xsl:template>
</xsl:stylesheet>